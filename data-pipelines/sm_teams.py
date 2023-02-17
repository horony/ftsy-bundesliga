#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Thu Jun 11 16:37:44 2020

Updates the dimension tables for teams (e.g. Eintracht Frankfurt or FC Bayern München)

@author: lennart
"""

import sys
sys.path.insert(1, './secrets/')
import requests
import json
import pandas as pd 
import time
from time import gmtime, strftime
from sqlalchemy import create_engine

#####################
#   GET META-DATA   #
#####################

print('\n>>> GET META-DATA <<<\n')

from sm_api_connection import sportmonks_token
response = requests.get("https://soccer.sportmonks.com/api/v2.0/leagues?api_token="+sportmonks_token)
print("API response code: " + str(response.status_code))

data = response.json()
league_id = data['data'][0]['id']
print("Current league-id is: "+ str(league_id))
season_id = data['data'][0]['current_season_id']
print("Current season-id is:"+ str(season_id))

#########################
#   GET DATA FROM API   #
#########################

print('\n>>> GET DATA FROM API <<<\n')

response = requests.get("https://soccer.sportmonks.com/api/v2.0/teams/season/"+str(season_id)+"?api_token="+sportmonks_token+"&include=coach")
print("API CALL Teams: " + str(response.status_code))

data = response.json()
data = data['data']

# Loop results and append to list

team_data = []

for team in data:
    team_list = []
    team_list.append(team['id'])
    team_list.append(team['name'])
    team_list.append(team['short_code'])
    team_list.append(team['founded'])
    team_list.append(team['venue_id'])
    team_list.append(team['logo_path'])
    team_list.append(team['coach']['data']['coach_id'])
    team_list.append(team['current_season_id'])    
    team_list.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))    
    team_data.append(team_list)
        
df_teams = pd.DataFrame(columns=['id','name', 'short_code', 'founded', 'venue_id', 'logo_path', 
                                 'current_coach_id', 'current_season_id', 'load_ts'], data=team_data)

##########################
#   WRITE INTO DATABASE  #
##########################

print('\n>>> WRITE INTO DATABASE <<<\n')

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# Create table if not exists
try:
    df_teams.to_sql(name='sm_teams', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_teams` ADD PRIMARY KEY (`id`);')
    message = 'Table created'

# If exists update table through temp table
except:
    df_teams.to_sql(name='tmp_sm_teams', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_teams` ADD PRIMARY KEY (`id`);')   
        con.execute('INSERT INTO sm_teams SELECT * FROM tmp_sm_teams t2 ON DUPLICATE KEY UPDATE load_ts = t2.load_ts, venue_id = t2.venue_id, current_coach_id = t2.current_coach_id, current_season_id = t2.current_season_id, logo_path = t2.logo_path;')    
        con.execute('DROP TABLE tmp_sm_teams;')    

    message = "Table updated"

finally:
    con.close()
  
print(message)
