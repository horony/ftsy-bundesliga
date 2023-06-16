#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Jun 10 16:25:01 2020

Updates the dimension table sm_seasons, containing Bundesliga seasons (e.g. 2022/2023)

@author: lennart
"""

import sys
sys.path.insert(1, '../secrets/')
from sm_api_connection import sportmonks_token

sys.path.insert(2, '../py/')
from logging_function import log, log_headline

import requests
import pandas as pd 
import time
from time import strftime
from sqlalchemy import create_engine

#########################
#   GET DATA FROM API   #
#########################

log_headline('(1/2) GET SEASON DATA + ROUND DATA FROM API')
log("Sending query to seasons endpoint")

response = requests.get(
    "https://api.sportmonks.com/v3/football/leagues/82"
    + "?api_token=" + sportmonks_token
    + "&include=currentSeason"
    )

log("API response code: " + str(response.status_code))
log("Processing season data")

data = response.json()
data = data['data']

# parsing league meta data from API call
league_data = []
league_data.append(data['id'])
league_data.append(data['name'])
league_data.append(data['image_path'])
league_data.append(data['currentseason']['id'])
league_data.append(data['currentseason']['name'])
league_data.append(int(data['currentseason']['is_current'] == 'True'))

# search for current round id and get round name
log("Sending query to stages endpoint")

response = requests.get(
    "https://api.sportmonks.com/v3/football/stages/seasons/"
    + str(data['currentseason']['id'])
    + "?api_token=" + sportmonks_token
    + "&include=currentRound"
    )

log("API response code: " + str(response.status_code))
log("Processing stage data + round data")

round_data = response.json()

# append current stage id
league_data.append(round_data['data'][0]['id'])

# append current round id and round name
league_data.append(round_data['data'][0]['currentround']['id'])
league_data.append(round_data['data'][0]['currentround']['name'])

# create load timestamp
league_data.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))    

# construct final DataFrame
log("Merging data into DataFrame")
 
df_seasons = pd.DataFrame(columns=['league_id','league_name', 'league_logo_path', 'season_id'
                                   , 'season_name', 'is_current_season', 'current_stage_id'
                                   , 'current_round_id', 'current_round_name', 'load_ts'], data=[league_data])

##########################
#   WRITE INTO DATABASE  #
##########################

log_headline('(2/2) WRITE INTO DATABASE')
log('Connecting to MySQL database')

# connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# create table if not exists
try:
    df_seasons.to_sql(name='sm_seasons', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_seasons` ADD PRIMARY KEY (`season_id`);')
    db_message = 'Table sm_seasons created'

# if exists update table through temp table
except:
    df_seasons.to_sql(name='tmp_sm_seasons', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_seasons` ADD PRIMARY KEY (`season_id`);')   
        con.execute('INSERT INTO sm_seasons SELECT * FROM tmp_sm_seasons t2 ON DUPLICATE KEY UPDATE league_logo_path = t2.league_logo_path, is_current_season = t2.is_current_season, current_stage_id = t2.current_stage_id, current_round_id = t2.current_round_id, current_round_name = t2.current_round_name, load_ts = t2.load_ts;')    
        con.execute('DROP TABLE tmp_sm_seasons;')    

    db_message = "Table sm_seasons updated"

finally:
    con.close()
  
log(db_message)