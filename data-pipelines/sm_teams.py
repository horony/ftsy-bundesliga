#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Thu Jun 11 16:37:44 2020

Updates the dimension tables for teams (e.g. Eintracht Frankfurt or FC Bayern München)

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
from time import gmtime, strftime
from sqlalchemy import create_engine

#####################
#   GET META-DATA   #
#####################

log_headline('(1/3) GET SEASON DATA FROM API')
log("Sending query to seasons endpoint")

response = requests.get(
    "https://api.sportmonks.com/v3/football/leagues/82"
    + "?api_token=" + sportmonks_token
    + "&include=currentSeason"
    )

log("API response code: " + str(response.status_code))
log("Processing season data")

data = response.json()
current_season_id = data['data']['currentseason']['id']

# search for current round id and get round name
log("The season_id is " + str(current_season_id))

#########################
#   GET DATA FROM API   #
#########################

log_headline('(2/3) GET TEAM DATA FROM API')
log("Sending query to teams endpoint")

response = requests.get(
    "https://api.sportmonks.com/v3/football/teams/seasons/"
    + str(current_season_id)
    + "?api_token=" + sportmonks_token
    )


log("API response code: " + str(response.status_code))
log("Processing venues data")

data = response.json()
data = data['data']

# loop results and append to list

team_data = []

for team in data:
    team_list = []
    team_list.append(team['id'])
    team_list.append(team['name'])
    team_list.append(team['short_code'])
    team_list.append(team['founded'])
    team_list.append(team['venue_id'])
    team_list.append(team['image_path'])
    team_list.append(None)
    team_list.append(current_season_id)    
    team_list.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))    
    team_data.append(team_list)
        
df_teams = pd.DataFrame(columns=['id','name', 'short_code', 'founded', 'venue_id', 'logo_path', 
                                 'current_coach_id', 'current_season_id', 'load_ts'], data=team_data)

log('Found ' + str(df_teams.shape[0]) + ' teams')

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
    df_teams.to_sql(name='sm_teams', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_teams` ADD PRIMARY KEY (`id`);')
    db_message = 'Table sm_teams created'

# if exists update table through temp table
except:
    df_teams.to_sql(name='tmp_sm_teams', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_teams` ADD PRIMARY KEY (`id`);')   
        con.execute('INSERT INTO sm_teams SELECT * FROM tmp_sm_teams t2 ON DUPLICATE KEY UPDATE load_ts = t2.load_ts, venue_id = t2.venue_id, current_coach_id = t2.current_coach_id, current_season_id = t2.current_season_id, logo_path = t2.logo_path;')    
        con.execute('DROP TABLE tmp_sm_teams;')    

    db_message = "Table sm_teams updated"

finally:
    con.close()
  
log(db_message)