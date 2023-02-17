#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Jun 10 19:18:56 2020

Updates the dimension tables for venues (e.g. Westfalenstadion Dortmund or Allianz Arena Munich)

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
current_round_id = data['data'][0]['current_round_id']
print("Current round-id is:"+ str(current_round_id))

#########################
#   GET DATA FROM API   #
#########################

print('\n>>> GET DATA FROM API <<<\n')

response = requests.get("https://soccer.sportmonks.com/api/v2.0/venues/season/"+str(season_id)+"?api_token="+sportmonks_token)
print("API response code: " + str(response.status_code))

data = response.json()
data = data['data']

# Loop results and append to list

venue_data = []

for venue in data:
    venue_list = []
    venue_list.append(venue['id'])
    venue_list.append(venue['name'])
    venue_list.append(venue['city'])
    venue_list.append(venue['address'])
    venue_list.append(venue['coordinates'])
    venue_list.append(venue['capacity'])
    venue_list.append(venue['surface'])
    venue_list.append(venue['image_path'])
    venue_list.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))    
    venue_data.append(venue_list)

# Safe results to DataFrame
df_venues = pd.DataFrame(columns=['id','name', 'city', 'address', 'coordinates', 'capacity', 'surface', 'image_path', 'load_ts'], data=venue_data)

##########################
#   WRITE INTO DATABASE  #
##########################

print('\n>>> WRITE INTO DATABASE <<<\n')

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# Create table if not exists
try:
    df_venues.to_sql(name='sm_venues', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_venues` ADD PRIMARY KEY (`id`);')
    message = 'Table sm_venues created'

# If exists update table through temp table
except:
    df_venues.to_sql(name='tmp_sm_venues', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_venues` ADD PRIMARY KEY (`id`);')   
        con.execute('INSERT INTO sm_venues SELECT * FROM tmp_sm_venues t2 ON DUPLICATE KEY UPDATE load_ts = t2.load_ts, name = t2.name, city = t2.city, address = t2.address, coordinates = t2.coordinates, capacity = t2.capacity, surface = t2.surface, image_path = t2.image_path;')    
        con.execute('DROP TABLE tmp_sm_venues;')    

    message = "Table sm_venues updated"

finally:
    con.close()
  
print(message)
