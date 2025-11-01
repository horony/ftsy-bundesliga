#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Jun 10 19:18:56 2020

Updates the dimension tables for venues (e.g. Westfalenstadion Dortmund or Allianz Arena Munich)

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

log_headline('(2/3) GET VENUE DATA FROM API')
log("Sending query to venues endpoint")

response = requests.get(
    "https://api.sportmonks.com/v3/football/venues/seasons/"
    + str(current_season_id)
    + "?api_token=" + sportmonks_token)

log("API response code: " + str(response.status_code))
log("Processing venues data")

data = response.json()
data = data['data']

# Loop results and append to list

venue_data = []

for venue in data:
    venue_list = []
    venue_list.append(venue['id'])
    venue_list.append(venue['name'])
    venue_list.append(venue['city_name'])
    venue_list.append(venue['address'])
    venue_list.append(str(venue['latitude']) + ',' + str(venue['longitude']))
    venue_list.append(venue['capacity'])
    venue_list.append(venue['surface'])
    venue_list.append(venue['image_path'])
    venue_list.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))    
    venue_data.append(venue_list)

# Safe results to DataFrame
df_venues = pd.DataFrame(columns=['id','name', 'city', 'address', 'coordinates', 'capacity', 'surface', 'image_path', 'load_ts'], data=venue_data)
log('Found ' + str(df_venues.shape[0]) + ' venues')

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
    df_venues.to_sql(name='sm_venues', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_venues` ADD PRIMARY KEY (`id`);')
        
    db_message = 'Table sm_venues created'

# if exists update table through temp table
except:
    df_venues.to_sql(name='tmp_sm_venues', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_venues` ADD PRIMARY KEY (`id`);')   
        con.execute('INSERT INTO sm_venues SELECT * FROM tmp_sm_venues t2 ON DUPLICATE KEY UPDATE load_ts = t2.load_ts, address = t2.address, coordinates = t2.coordinates, capacity = t2.capacity, surface = t2.surface, image_path = t2.image_path;')    
        con.execute('DROP TABLE tmp_sm_venues;')

    db_message = "Table sm_venues updated"

finally:
    con.close()
  
log(db_message)