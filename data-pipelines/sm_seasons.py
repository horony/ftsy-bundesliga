#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Jun 10 16:25:01 2020

Updates the dimension tables for Bundesliga seasons (e.g. 2019/2020)

@author: lennart
"""

import sys
sys.path.insert(1, './secrets/')
import requests
import pandas as pd 
import time
from time import gmtime, strftime
from sqlalchemy import create_engine

#########################
#   GET DATA FROM API   #
#########################

print('\n>>> GET DATA FROM API <<<\n')

from sm_api_connection import sportmonks_token
print("API Query for seasons")
response = requests.get("https://soccer.sportmonks.com/api/v2.0/leagues/82?api_token="+sportmonks_token+"&include=season")
print("API response code: " + str(response.status_code))

data = response.json()
data = data['data']

# Parsing data from API call
league_data = []
league_data.append(data['id'])
league_data.append(data['name'])
league_data.append(data['logo_path'])
league_data.append(data['current_season_id'])
league_data.append(data['season']['data']['name'])
league_data.append(data['season']['data']['is_current_season'])

if data['current_stage_id'] is None:
    league_data.append(None)
else:
    league_data.append(data['current_stage_id'])

if data['current_round_id'] is None:
    league_data.append(None)
else:
    league_data.append(data['current_round_id'])

# Search for current round id and get round name
print("API Query for rounds")
response = requests.get("https://soccer.sportmonks.com/api/v2.0/seasons/"+str(data['current_season_id'])+"?api_token="+sportmonks_token+"&include=rounds")
print("API response code: " + str(response.status_code))

# Parsing data from API call
round_data = response.json()
round_data = round_data['data']['rounds']

if data['current_round_id'] is None:
    league_data.append(1)
else:
    for i in round_data['data']:
        if (i['id']) == data['current_round_id']:
            league_data.append(i['name'])

league_data.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))    
        
# Construct final DataFrame     
df_seasons = pd.DataFrame(columns=['league_id','league_name', 'league_logo_path', 'season_id'
                                   , 'season_name', 'is_current_season', 'current_stage_id'
                                   , 'current_round_id', 'current_round_name', 'load_ts'], data=[league_data])

##########################
#   WRITE INTO DATABASE  #
##########################

print('\n>>> WRITE INTO DATABASE <<<\n')

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# Create table if not exists
try:
    df_seasons.to_sql(name='sm_seasons', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_seasons` ADD PRIMARY KEY (`season_id`);')
    message = 'Table sm_seasons created'

# If exists update table through temp table
except:
    df_seasons.to_sql(name='tmp_sm_seasons', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_seasons` ADD PRIMARY KEY (`season_id`);')   
        con.execute('INSERT INTO sm_seasons SELECT * FROM tmp_sm_seasons t2 ON DUPLICATE KEY UPDATE league_logo_path = t2.league_logo_path, is_current_season = t2.is_current_season, current_stage_id = t2.current_stage_id, current_round_id = t2.current_round_id, current_round_name = t2.current_round_name, load_ts = t2.load_ts;')    
        con.execute('DROP TABLE tmp_sm_seasons;')    

    message = "Table sm_seasons updated"

finally:
    con.close()
  
print(message)
