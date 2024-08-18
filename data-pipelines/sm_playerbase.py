#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created for API 2.0 on Thu Jun 11 16:37:44 2020
Updated for API 3.0 on Thu Jul 13 23:56:33 2023

General purpose is updating the table sm_playerbase containing all currently active players in Bundesliga and informations on the player.
In doing so also the table sm_player_transfers is updated cotaining all transfers of currently acitve players in Bundesliga.

@author: lennart
"""

import pandas as pd

from sqlalchemy import create_engine
import requests
import numpy as np
 
import sys
sys.path.insert(1, '../secrets/')
from sm_api_connection import sportmonks_token

sys.path.insert(2, '../py/')
from logging_function import log, log_headline
from dataprep_functions import isNone


###############################
#   METAINFOS FROM MYSQL DB   #
###############################

log_headline('(1/4) GET META-DATA FROM MYSQL DB')
log('Connecting to MySQL database')

# connect to MySQL-database

from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  


with engine.connect() as con:
    log("Selecting current season_id from table parameter")
    sql_select = con.execute('SELECT season_id FROM parameter')
    sql_first_row = sql_select.fetchone()
    aktuelle_buli_season =  sql_first_row['season_id']

con.close()

"""  
# uncomment for testing
aktuelle_buli_season = 21795
"""

log("Current season-id in fantasy-game: " + str(aktuelle_buli_season))

########################################################
#   GET TEAMS AND SIDELINED DATA FROM TEAMS ENDPOINT   #
########################################################

log_headline('(2/4) GET TEAM-IDS AND SIDELINED DATA FROM API')
log("Sending query to teams endpoint")

response_teams = requests.get(
    "https://api.sportmonks.com/v3/football/teams/seasons/"
    + str(aktuelle_buli_season)
    + "?api_token=" + sportmonks_token
    + "&include=sidelined.type;sidelined.player"
)

log("API response code: " + str(response_teams.status_code))

log("Processing teams data and sidelined data")
data_teams = response_teams.json()['data']

list_teams = []
list_sidelined = []

for team in data_teams:
        
    ####################
    #   1. TEAM DATA   #
    ####################
    
    # set up list to collect currently parsed team data
    list_current_team = []
    
    # team_id and team name
    list_current_team.append(team['id'])
    list_current_team.append(team['name'])
    
    list_teams.append(list_current_team)  
    
    #########################
    #   2. SIDELINED DATA   #
    #########################
    
    if team.get("sidelined") is not None and len(team['sidelined']):

        # loop trough transfers of player
        for player_sidelined in team['sidelined']:
            
            # set up list to collect currently parsed player data
            list_current_sidelined = []
            
            list_current_sidelined.append(player_sidelined['player_id'])
            list_current_sidelined.append(player_sidelined['player']['display_name'])
            list_current_sidelined.append(player_sidelined['type']['id'])            
            list_current_sidelined.append(player_sidelined['type']['name'])
            list_current_sidelined.append(player_sidelined['start_date'])
            list_current_sidelined.append(player_sidelined['end_date'])
            list_current_sidelined.append(player_sidelined['completed'])
               
            list_sidelined.append(list_current_sidelined)      

log('Extracting teamd-ids')              
df_teams = pd.DataFrame(columns=['team_id','name'], data=list_teams)    
aktuelle_buli_teams = df_teams['team_id'].tolist()
log("Current team-ids are: " + str(aktuelle_buli_teams))

log('Building DataFrame from parsed sidelined data')              
df_sidelined = pd.DataFrame(columns=['player_id','display_name','sidelined_type_id','injury_reason','start_date','end_date','completed'], data=list_sidelined)
df_sidelined = df_sidelined.drop_duplicates(subset=['player_id'], keep='first')
log('Created DataFrame containing ' + str(df_sidelined.shape[0]) + ' unique sidelined players')

##############################################################
#   GET PLAYER DATA AND TRANSFER DATA FROM SQUADS ENDPOINT   #
##############################################################

log_headline('(3/4) GET SQUADS FROM API')
log("Sending query to squads endpoint for each team")

list_players = []
list_transfers = []
cnt_processed_teams = 1

print('')

# iterate through teams
for team_id in aktuelle_buli_teams:

    log('Processing team ' + str(cnt_processed_teams) + '/' + str(len(aktuelle_buli_teams)))
    
    # query squads endpoint with team_id
    log('.. Sending query to squads endpoint')
    
    response_sqds = requests.get(
        "https://api.sportmonks.com/v3/football/squads"
        + "/teams/" + str(team_id) 
        + "?api_token=" + sportmonks_token
        + "&include=team;player.transfers;player.country;player.city;player.nationality;player.metadata;position;detailedPosition"
        )
    
    log(".. API response code: " + str(response_sqds.status_code))

    data_sqds = response_sqds.json()['data']
    
    # iterate through players in squad
    
    log('.. Parsing player data')
    for player in data_sqds:
        
        ######################
        #   1. PLAYER DATA   #
        ######################
        
        # set up list to collect currently parsed player data
        list_current_player = []
        
        # player_id
        list_current_player.append(player['player_id'])
        
        # player is on squad
        list_current_player.append(1)
        
        # player_name
        list_current_player.append(player['player']['name'])        
        list_current_player.append(player['player']['common_name'])
        list_current_player.append(player['player']['display_name'])        
        list_current_player.append(player['player']['firstname'])        
        list_current_player.append(player['player']['lastname'])

        # current team & jersey number
        list_current_player.append(player['team_id'])
        list_current_player.append(player['team']['name'])
        list_current_player.append(isNone(player['jersey_number'], None))

        # position        
        list_current_player.append(isNone(player['position_id'], None))
        
        # recode player positions to German namings
        if player['position'] is None:
            list_current_player.append(None)
            list_current_player.append(None)
        else:
            if player['position']['id'] == 24:
                list_current_player.append('TW')
                list_current_player.append('Torwart')
            elif player['position']['id'] == 25:
                list_current_player.append('AW')
                list_current_player.append('Abwehr')
            elif player['position']['id'] == 26:
                list_current_player.append('MF')
                list_current_player.append('Mittelfeld')
            elif player['position']['id'] == 27:
                list_current_player.append('ST')
                list_current_player.append('Sturm')
            else:
                list_current_player.append(None)
                list_current_player.append(None)
                
        # detailed position
        if player.get("detailedposition") is not None and len(player['detailedposition']):
            list_current_player.append(isNone(player['detailedposition']['id'],None)) 
            list_current_player.append(isNone(player['detailedposition']['name'],None)) 
        else:
            list_current_player.append(None)
            list_current_player.append(None)           
        
        list_current_player.append(None) # captain

        """
        Sidelined is calced on full DataFrames after parsing, just placeholders here
        """ 
        
        list_current_player.append(None) # placeholder
        list_current_player.append(None) # placeholder
        list_current_player.append(None) # placeholder
        list_current_player.append(False) # placeholder
        
        # player image
        list_current_player.append(isNone(player['player']['image_path'],None))

        # height and weight        
        list_current_player.append(isNone(player['player']['height'],None))       
        list_current_player.append(isNone(player['player']['weight'],None)) 
        
        # nationality, birth date and place of birth
        list_current_player.append(isNone(player['player']['nationality_id'],None)) 
        
        if player['player']['nationality'] is not None:
            list_current_player.append(player['player']['nationality']['name']) 
        else:
            list_current_player.append(None) 

        list_current_player.append(isNone(player['player']['date_of_birth'],None)) 

        if player['player']['city'] is not None:
            list_current_player.append(player['player']['city']['name']) 
        else:
            list_current_player.append(None) 
        
        # add data to list
        list_players.append(list_current_player)
        
        ########################
        #   2. TRANSFER DATA   #
        ########################
        
        # check if key 'transfers' exists
        if player['player'].get("transfers") is not None and len(player['player']['transfers']):

            # loop trough transfers of player
            for transfer in player['player']['transfers']:
                
                # set up list to collect currently parsed player data
                list_current_transfers = []
                
                # transfer_id
                list_current_transfers.append(transfer['id'])
                
                # player_id and player name
                list_current_transfers.append(player['player_id'])
                list_current_transfers.append(player['player']['common_name'])
                
                # infos on transfer
                list_current_transfers.append(transfer['date'])
                list_current_transfers.append(transfer['from_team_id'])
                list_current_transfers.append(transfer['to_team_id'])
                
                # type 
                if transfer['type_id'] == 218:
                    list_current_transfers.append('Leihe')
                elif transfer['type_id'] == 219:
                    list_current_transfers.append('Transfer')
                elif transfer['type_id'] == 220:
                    list_current_transfers.append('Ablösefrei')
                else:
                    list_current_transfers.append(None)
                
                # amount paid
                if transfer['amount'] is not None and transfer['type_id'] != 220:
                    list_current_transfers.append(int(transfer['amount']))
                elif transfer['amount'] is not None and transfer['type_id'] == 220:
                    list_current_transfers.append(0)
                else:
                    list_current_transfers.append(None)
                    
                # add data to list
                list_transfers.append(list_current_transfers)  
    
    cnt_processed_teams = cnt_processed_teams + 1
    
print('')

# store player results to DataFrame
log('Building DataFrame from parsed player data')              
column_names_players = ['id','rostered','fullname','common_name','display_name'
                      ,'firstname','lastname','current_team_id','current_team_name'
                      ,'number','position_id','position_short', 'position_long','position_detail_id','position_detail_name'
                      ,'captain','injured', 'is_suspended', 'injury_reason', 'is_sidelined'
                      ,'image_path','height','weight'
                      ,'country_id','birthcountry','birth_dt','birthplace'
                      ]
df_players = pd.DataFrame(columns=column_names_players, data=list_players)
log('Created DataFrame containing ' + str(df_players.shape[0]) + ' players')

# joining sidelined-data to player-data to update sidelined information
log('Updating player DataFrame with sidelined DataFrame')              

# filter df_sidelined to valid rows and columns
df_sidelined = df_sidelined.loc[~df_sidelined.completed]
df_sidelined = df_sidelined[['player_id','sidelined_type_id','injury_reason']]

# execute join
df_players = pd.merge(df_players, df_sidelined, left_on='id', right_on='player_id', how='left')

# define suspensions (e.g. red card)
df_players['is_suspended'] = np.where((df_players['sidelined_type_id'] == 561) | (df_players['sidelined_type_id'] == 1692), 1, None)

# define injuries
df_players['injury_reason_x'] = df_players['injury_reason_y']
df_players = df_players.rename(columns={'injury_reason_x': 'injury_reason'})
df_players['injured'] = np.where(((df_players['sidelined_type_id'] > 0) & (df_players['sidelined_type_id'] != 561) & (df_players['sidelined_type_id'] != 1692)), 1, None)

# drop not needed columns
df_players = df_players.drop(columns=['player_id','sidelined_type_id','injury_reason_y'], axis=1)
log('Updated player DataFrame with sidelined information of ' + str(df_sidelined.shape[0]) + ' players')

# store transfers results to DataFrame
log('Building DataFrame from parsed transfer data')              
column_names_transfers = ['transfer_id','player_id','player_common_name','transfer_dt','from_team_id','to_team_id','transfer_type','amount']
df_transfers = pd.DataFrame(columns=column_names_transfers, data=list_transfers)
df_transfers = df_transfers.dropna(subset=['to_team_id'], inplace=True)
df_transfers = df_transfers.drop_duplicates(subset=['transfer_id'], keep='first')
log('Created DataFrame containing ' + str(df_transfers.shape[0]) + ' transfers')

##########################
#   WRITE INTO DATABASE  #
##########################

log_headline('(4/4) WRITE INTO DATABASE')
log('Connecting to MySQL database')     

# connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# 1.) transfers
log('Starting refresh process for sm_player_transfers ')

# create tmp table needed for update
log('Creating tmp table tmp_sm_player_transfers')
df_transfers.to_sql(name='tmp_sm_player_transfers', con=engine, index=False, if_exists='replace')

with engine.connect() as con:
    
    # set collation and primary key for tmp table
    log('Setting primary key for tmp_sm_player_transfers to transfer_id')
    con.execute('ALTER TABLE `tmp_sm_player_transfers` ADD PRIMARY KEY (`transfer_id`);')
    log('Setting collation for tmp_sm_player_transfers to utf8mb4_unicode_520_ci')    
    con.execute('ALTER TABLE `tmp_sm_player_transfers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;')
    
    # updating prod table through tmp table
    log('Executing INSERT + UPDATE on sm_player_transfers')
    con.execute('''
                INSERT INTO xa7580_db1.sm_player_transfers 
                SELECT  t2.transfer_id
                        , t2.player_id
                        , t2.player_common_name
                        , t2.transfer_dt
                        , t2.from_team_id
                        , t2.to_team_id
                        , t2.transfer_type
                        , t2.amount
                        , sysdate() as insert_ts
                        , null as update_ts
                        
                FROM tmp_sm_player_transfers t2 
                
                ON DUPLICATE KEY UPDATE 
                    player_id = t2.player_id
                    , player_common_name = t2.player_common_name
                    , transfer_dt = t2.transfer_dt
                    , from_team_id = t2.from_team_id
                    , to_team_id = t2.to_team_id
                    , transfer_type = t2.transfer_type
                    , amount = t2.amount
                    , update_ts = sysdate()    
                ;
                ''')    

    # drop tmp table as not needed anymore
    log('Dropping tmp table tmp_sm_player_transfers')
    con.execute('DROP TABLE tmp_sm_player_transfers;')    

log('Finished refresh process for sm_player_transfers')
print('')

# 2.) players
log('Starting refresh process for sm_playerbase ')

# create tmp table need for update
log('Creating tmp table tmp_sm_playerbase')
df_players.to_sql(name='tmp_sm_playerbase', con=engine, index=False, if_exists='replace')

with engine.connect() as con:
    
    # set collation and primary key for tmp table
    log('Setting primary key for tmp_sm_playerbase to id')
    con.execute('ALTER TABLE `tmp_sm_playerbase` ADD PRIMARY KEY (`id`);')
    log('Setting collation for tmp_sm_playerbase to utf8mb4_unicode_520_ci')
    con.execute('ALTER TABLE `tmp_sm_playerbase` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;')    

    # updating prod table through tmp table
    log('Executing INSERT + UPDATE on sm_playerbase')    
    con.execute('''
                INSERT INTO sm_playerbase 
                SELECT  id
                        , rostered 
                        , fullname
                        , common_name
                        , display_name
                        , firstname
                        , lastname
                        , current_team_id
                        , current_team_name
                        , number
                        , position_id
                        , position_short
                        , position_long
                        , position_detail_id
                        , position_detail_name
                        , captain
                        , injured
                        , is_suspended
                        , injury_reason
                        , is_sidelined
                        , image_path
                        , height
                        , weight
                        , country_id
                        , birthcountry
                        , birth_dt
                        , birthplace
                        , sysdate() as insert_ts
                        , null as update_ts
                        
                FROM tmp_sm_playerbase t2 
                
                ON DUPLICATE KEY UPDATE 
                    rostered = t2.rostered
                    , fullname = t2.fullname
                    , common_name = t2.common_name
                    , firstname = t2.firstname
                    , lastname = t2.lastname
                    , current_team_id = t2.current_team_id
                    , current_team_name = t2.current_team_name
                    , number = t2.number
                    , captain = t2.captain
                    , injured = t2.injured
                    , is_suspended = t2.is_suspended
                    , injury_reason = t2.injury_reason
                    , is_sidelined = t2.is_sidelined
                    , image_path = t2.image_path
                    , position_detail_id = t2.position_detail_id
                    , position_detail_name = t2.position_detail_name
                    , height = t2.height
                    , weight = t2.weight
                    , country_id = t2.country_id
                    , birthcountry = t2.birthcountry
                    , birth_dt = t2.birth_dt
                    , birthplace = t2.birthplace
                    , update_ts = sysdate()
                ;
                ''')    
                
    # setting rostered = 0 if player is currently not on a squad
    log('Setting variable sm_playerbase.rostered = 0 if player is not in tmp table tmp_sm_playerbase')    
    con.execute('''
                UPDATE  sm_playerbase 
                SET     rostered = 0
                        , current_team_id = NULL
                        , current_team_name = NULL
                        , captain = NULL
                        , injured = NULL
                        , is_suspended = NULL
                        , injury_reason = NULL
                        , is_sidelined = NULL 
                WHERE id NOT IN (SELECT id from tmp_sm_playerbase)
                ;
                ''')
                
    # drop tmp table as not needed anymore
    log('Dropping tmp table tmp_sm_playerbase')
    con.execute('DROP TABLE tmp_sm_playerbase;')    

log('Finished refresh process for sm_playerbase')

con.close()
