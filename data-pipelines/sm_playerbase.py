#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Thu Jun 11 16:37:44 2020

Updates the dimension tables for Bundesliga players (e.g. Robert Lewandowski) and coaches (e.g. Julian Nagelsmann).
Addionatly also the transfer history of each player is updated.

@author: lennart
"""

import sys
sys.path.insert(1, './secrets/')
import requests
import pandas as pd 
import json
import time
from time import gmtime, strftime
from datetime import datetime
from sqlalchemy import create_engine

###############################
#   METAINFOS FROM MYSQL DB   #
###############################

print('\n>>> GET META-DATA FROM MYSQL DB<<<\n')

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

with engine.connect() as con:
    sql_select = con.execute('SELECT spieltag FROM parameter')
    sql_first_row = sql_select.fetchone()
    aktueller_fantasy_spieltag =  sql_first_row['spieltag']
    
    sql_select = con.execute('SELECT season_id FROM sm_seasons WHERE is_current_season = 1')
    sql_first_row = sql_select.fetchone()
    aktuelle_buli_season =  sql_first_row['season_id']
        
        
print("Current round in fantasy-game:", aktueller_fantasy_spieltag)
print("Current season-id in fantasy-game:", aktuelle_buli_season)

#########################
#   GET DATA FROM API   #
#########################

from sm_api_connection import sportmonks_token
print("API Query for squads")
response = requests.get("https://soccer.sportmonks.com/api/v2.0/teams/season/"+str(aktuelle_buli_season)+"?api_token="+sportmonks_token+"&include=coach,squad.player.sidelined,squad.player.transfers")
print("API response code: " + str(response.status_code))

squad_response = response.json()
squad_response = squad_response['data']


# Loop each Bundesliga squad for players, coaches and player transfer-history and append to list

squad_data = []
coach_data = []
player_transfer_data = []

for team in squad_response:
    
    current_team_id = team['id']
    current_team_name = team['name']

    # Get coach data from squad
    coach_list = []
    coach_list.append(team['coach']['data']['coach_id'])
    coach_list.append(team['coach']['data']['fullname'])
    coach_list.append(team['coach']['data']['common_name'])
    coach_list.append(team['coach']['data']['firstname'])
    coach_list.append(team['coach']['data']['lastname'])
    coach_list.append(team['coach']['data']['image_path'])
    coach_list.append(current_team_id)
    coach_list.append(team['coach']['data']['birthdate'])
    coach_list.append(team['coach']['data']['birthplace'])
    coach_list.append(team['coach']['data']['birthcountry'])
    coach_list.append(team['coach']['data']['nationality'])
    coach_list.append(team['coach']['data']['country_id'])      
    coach_data.append(coach_list)
    
    # Get player data for every player on squad
    for player in team['squad']['data']:
        squad_list = []
        transfer_list = []

        squad_list.append(player['player_id'])
        current_player_id = player['player_id']
               
        squad_list.append(1)
        squad_list.append(player['player']['data']['fullname'])        
        squad_list.append(player['player']['data']['common_name'])
        current_player_name = player['player']['data']['common_name']
        
        squad_list.append(player['player']['data']['display_name'])        
        squad_list.append(player['player']['data']['firstname'])        
        squad_list.append(player['player']['data']['lastname'])        
        squad_list.append(current_team_id)
        squad_list.append(current_team_name)
        squad_list.append(player['number'])        
        squad_list.append(player['position_id'])

        #  Recode player positions
        if player['position_id'] == 1:
            squad_list.append('TW')
        elif player['position_id'] == 2:
            squad_list.append('AW')
        elif player['position_id'] == 3:
            squad_list.append('MF')
        elif player['position_id'] == 4:
            squad_list.append('ST')
        else:
            squad_list.append(None)

        #  Recode player positions
        if player['position_id'] == 1:
            squad_list.append('Torwart')
        elif player['position_id'] == 2:
            squad_list.append('Abwehr')
        elif player['position_id'] == 3:
            squad_list.append('Mittelfeld')
        elif player['position_id'] == 4:
            squad_list.append('Sturm')
        else:
            squad_list.append(None)
            
        squad_list.append(player['captain'])
        squad_list.append(player['injured'])
        
        suspension_list = [None]
        sidelined_other_list = [None]
        
        # If player is sidelined get additional information, categorize in injuries and suspensions and append most recent 
        for sidelined in player['player']['data']['sidelined']['data']:
            
            start_dt = datetime.strptime(sidelined['start_date'], '%Y-%m-%d') 
            if sidelined['end_date'] is None:
                end_dt = datetime.strptime('2099-12-31', '%Y-%m-%d') 
            else:
                end_dt = datetime.strptime(sidelined['end_date'], '%Y-%m-%d') 
            
            if start_dt <= datetime.now() and end_dt >= datetime.now():
                if sidelined['description'].upper() == 'SUSPENDED':
                    suspension_list.append(sidelined['description'])
                else:
                    sidelined_other_list.append(sidelined['description'])
        
        squad_list.append(suspension_list[-1])
        squad_list.append(sidelined_other_list[-1])
        
        if suspension_list[-1] is not None or sidelined_other_list[-1] is not None:
            squad_list.append(True)
        else:
            squad_list.append(False)
            
        squad_list.append(player['player']['data']['image_path'])        
        squad_list.append(player['player']['data']['height'])        
        squad_list.append(player['player']['data']['weight'])                
        squad_list.append(player['player']['data']['country_id'])        
        squad_list.append(player['player']['data']['birthcountry'])
        if player['player']['data']['birthdate'] is None:
            squad_list.append(None)
        else:
            squad_list.append(datetime.strptime(player['player']['data']['birthdate'] , '%d/%m/%Y').date())
        squad_list.append(player['player']['data']['birthplace'])
        squad_list.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))    
        
        squad_data.append(squad_list)
        
        # Get transfer history of each player
        for transfer in player['player']['data']['transfers']['data']:
            transfer_list = []

            transfer_list.append(current_player_id)
            transfer_list.append(current_player_name)
            transfer_list.append(datetime.strptime(transfer['date'], '%Y-%m-%d'))
            transfer_list.append(transfer['from_team_id'])
            transfer_list.append(transfer['to_team_id'])

            # Recode transfer type
            if transfer['transfer'] == 'loan':
                transfer_type = 'Leihe'
            elif transfer['transfer'] in ['bought','sold']:
                transfer_type = 'Transfer'
            elif transfer['transfer'] == 'N/A':
                transfer_type = None
            elif transfer['transfer'] == 'free':
                transfer_type = 'Ablösefrei'
            else:
                transfer_type = None
            transfer_list.append(transfer_type)
            transfer_list.append(transfer['amount'])
            transfer_list.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))    

            player_transfer_data.append(transfer_list)

# Setup the three DataFrames

column_names_coaches = ['id','fullname','common_name','firstname','lastname','image_path','current_team_id',
                        'birth_dt','birthplace','birthcountry', 'nationality', 'country_id']
df_coaches = pd.DataFrame(columns=column_names_coaches, data=coach_data)     

column_names_squad = ['id','rostered','fullname','common_name','display_name','firstname','lastname','current_team_id','current_team_name',
                'number','position_id','position_short', 'position_long', 'captain','injured', 'is_suspended', 'injury_reason', 'is_sidelined', 'image_path','height','weight',
                'country_id','birthcountry','birth_dt','birthplace','load_ts']
df_squads = pd.DataFrame(columns=column_names_squad, data=squad_data)

column_names_transfers = ['player_id','player_common_name','transfer_dt','from_team_id','to_team_id','transfer_type','amount','load_ts']
df_transfers = pd.DataFrame(columns=column_names_transfers, data=player_transfer_data)
df_transfers = df_transfers.drop_duplicates(subset=['player_id', 'transfer_dt', 'from_team_id', 'to_team_id'], keep='first')

##########################
#   WRITE INTO DATABASE  #
##########################

print('\n>>> WRITE INTO DATABASE <<<\n')

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# TRANSFERS

# Create table if not exists 
try:
    df_transfers.to_sql(name='sm_player_transfers', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_player_transfers` ADD PRIMARY KEY (`player_id`,`transfer_dt`,`to_team_id`,`from_team_id`)')
        con.execute('ALTER TABLE xa7580_db1.`sm_player_transfers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;')        
    message = 'Table sm_player_transfers created'

# If exists update table through temp table
except:
    df_transfers.to_sql(name='tmp_sm_player_transfers', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_player_transfers` ADD PRIMARY KEY (`player_id`,`transfer_dt`,`to_team_id`,`from_team_id`);')
        con.execute('ALTER TABLE xa7580_db1.`tmp_sm_player_transfers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;')
        con.execute('INSERT INTO sm_player_transfers SELECT * FROM tmp_sm_player_transfers t2 ON DUPLICATE KEY UPDATE player_common_name=t2.player_common_name, transfer_type=t2.transfer_type, amount=t2.amount;')    
        #con.execute('UPDATE sm_coches SET current_team_id = NULL WHERE id NOT IN (SELECT id from tmp_sm_coaches)')
        con.execute('DROP TABLE tmp_sm_player_transfers;')    
    message = "Table sm_player_transfers updated"

print(message)

# COACHES

# Create table if not exists
try:
    df_coaches.to_sql(name='sm_coaches', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_coaches` ADD PRIMARY KEY (`id`);')
        con.execute('ALTER TABLE xa7580_db1.`sm_coaches` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;')        
    message = 'Table sm_coaches created'

# If exists update table through temp table
except:
    df_coaches.to_sql(name='tmp_sm_coaches', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_coaches` ADD PRIMARY KEY (`id`);')
        con.execute('ALTER TABLE xa7580_db1.`tmp_sm_coaches` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;')
        con.execute('INSERT INTO sm_coaches SELECT * FROM tmp_sm_coaches t2 ON DUPLICATE KEY UPDATE fullname=t2.fullname, common_name=t2.common_name, firstname=t2.firstname, lastname=t2.lastname, current_team_id=t2.current_team_id, image_path=t2.image_path, country_id=t2.country_id, nationality=t2.nationality, birthcountry=t2.birthcountry, birth_dt=t2.birth_dt, birthplace=t2.birthplace;')    
        con.execute('UPDATE sm_coaches SET current_team_id = NULL WHERE id NOT IN (SELECT id from tmp_sm_coaches)')
        con.execute('DROP TABLE tmp_sm_coaches;')    
    message = "Table sm_coaches updated"

print(message)

# PLAYERS

# Create table if not exists
try:
    df_squads.to_sql(name='sm_playerbase', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_playerbase` ADD PRIMARY KEY (`id`);')
        con.execute('ALTER TABLE xa7580_db1.`sm_playerbase` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;')
        
    message = 'Table sm_playerbase created'

# If exists update table through temp table
except:
    df_squads.to_sql(name='tmp_sm_playerbase', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_playerbase` ADD PRIMARY KEY (`id`);')
        con.execute('ALTER TABLE xa7580_db1.`tmp_sm_playerbase` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;')
        con.execute('INSERT INTO sm_playerbase SELECT * FROM tmp_sm_playerbase t2 ON DUPLICATE KEY UPDATE rostered=t2.rostered,fullname=t2.fullname, common_name=t2.common_name, firstname=t2.firstname, lastname=t2.lastname, current_team_id=t2.current_team_id, current_team_name=t2.current_team_name,number=t2.number, captain=t2.captain, injured=t2.injured, is_suspended=t2.is_suspended, injury_reason=t2.injury_reason, is_sidelined=t2.is_sidelined, image_path=t2.image_path, height=t2.height, weight=t2.weight,country_id=t2.country_id, birthcountry=t2.birthcountry, birth_dt=t2.birth_dt, birthplace=t2.birthplace;')    
        con.execute('UPDATE sm_playerbase SET rostered = 0,current_team_id = NULL, current_team_name = NULL, captain = NULL, injured=NULL, is_suspended=NULL, injury_reason=NULL, is_sidelined=NULL WHERE id NOT IN (SELECT id from tmp_sm_playerbase)')
        con.execute('DROP TABLE tmp_sm_playerbase;')    
    message = "Table sm_playerbase updated"

finally:
    con.close()
print(message)
