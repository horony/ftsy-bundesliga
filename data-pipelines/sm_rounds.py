#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Mon Jul  3 23:48:50 2023

@author: lennart

Updates the dimension tables for Bundesliga rounds (e.g. Spieltag 15)
    
"""

import sys
sys.path.insert(1, '../secrets/')
from sm_api_connection import sportmonks_token

sys.path.insert(2, '../py/')
from logging_function import log, log_headline
from dataprep_functions import isNone

import requests
import pandas as pd 
from datetime import datetime
import time
from time import gmtime, strftime
from sqlalchemy import create_engine
import sys

###############################
#   METAINFOS FROM MYSQL DB   #
###############################

log_headline('(1/3) GET META-DATA FROM MYSQL DB')
log('Connecting to MySQL database')

# connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

with engine.connect() as con:
    log("Selecting current season_id from table parameter")
    sql_select = con.execute('SELECT season_id FROM parameter')
    sql_first_row = sql_select.fetchone()
    aktuelle_buli_season =  sql_first_row['season_id']
  
"""
# uncomment for testing
aktuelle_buli_season = 19744
"""

log("Current season-id in fantasy-game: " + str(aktuelle_buli_season))

##################
#   GET ROUNDS   #
##################

log_headline('(2/3) GET ROUND DATA FROM API')
log("Sending query to rounds endpoint")

response = requests.get(
    "https://api.sportmonks.com/v3/football/rounds/seasons/"
    + str(aktuelle_buli_season)
    + "?api_token=" + sportmonks_token
    + "&include=stage;fixtures"
    )

log("API response code: " + str(response.status_code))
log("Processing rounds data")

response_round = response.json()
data_round = response_round['data']

list_round = []

# Loop through single rounds
for spieltag in data_round:
    
    list_round_parsed = []
    
    # season_id
    list_round_parsed.append(spieltag['season_id'])
    
    # round_id and round name
    list_round_parsed.append(spieltag['id'])
    list_round_parsed.append(int(spieltag['name']))
    
    # start_dt and end_dt
    if spieltag['starting_at'] is not None:
        list_round_parsed.append(datetime.strptime(spieltag['starting_at'], '%Y-%m-%d').date())
    else:
        list_round_parsed.append(None)
        
    if spieltag['ending_at'] is not None:
        list_round_parsed.append(datetime.strptime(spieltag['ending_at'], '%Y-%m-%d').date())
    else:
        list_round_parsed.append(None)
     
    # rounde state
    list_round_parsed.append(isNone(spieltag['finished'],None))
    
    # fantasy info
    list_round_parsed.append(True)    
    list_round_parsed.append(False)  
    list_round_parsed.append(True)   
    
    # league_id and stage_id
    list_round_parsed.append(isNone(spieltag['league_id'],None))
    list_round_parsed.append(isNone(spieltag['stage_id'],None))
    
    # load_ts
    list_round_parsed.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))

    # append parsed data to list_round
    list_round.append(list_round_parsed)

# safe results to DataFrame
df_rounds = pd.DataFrame(columns=['season_id', 'id', 'name', 'start_dt', 'end_dt', 'is_round_complete', 'is_fantasy_league_round', 'is_fantasy_cup_round', 'is_fantasy_active', 'league_id', 'stage_id', 'load_ts'], data=list_round)
log('Found ' + str(df_rounds.shape[0]) + ' rounds')

##########################
#   WRITE INTO DATABASE  #
##########################

log_headline('(3/3) WRITE INTO DATABASE')
log('Connecting to MySQL database')

# connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# create table if not exists
try:
    df_rounds.to_sql(name='sm_rounds', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_rounds` ADD PRIMARY KEY (`season_id`,`id`,`league_id`,`stage_id`);')
    db_message = 'Table sm_rounds created'

# if exists update table through temp table
except:
    df_rounds.to_sql(name='tmp_sm_rounds', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_rounds` ADD PRIMARY KEY (`season_id`,`id`,`league_id`,`stage_id`);')   
        con.execute('''
                    INSERT INTO sm_rounds 
                    SELECT * FROM tmp_sm_rounds t2 
                    ON DUPLICATE KEY UPDATE  
                        start_dt = t2.start_dt
                        , end_dt = t2.end_dt
                        , is_round_complete = t2.is_round_complete
                    ;
                    ''')    
        con.execute('DROP TABLE tmp_sm_rounds;')    
        
    db_message = "Table sm_rounds updated"

finally:
    con.close()
  
log(db_message)
print('')