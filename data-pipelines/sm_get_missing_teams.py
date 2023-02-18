#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Jun 10 16:25:01 2020

Updates the teams dimension table with missing teams through a scheduled cronjob.
Initial ETL covers just Bundesliga teams and IDs for non-Bundesliga teams. This script covers all teams. Needed for transfers history.
As a full load would go beyond API limits, therefore this script limits the number of queries.

@author: lennart
"""

import sys
sys.path.insert(1, './secrets/')
import requests
from sqlalchemy import create_engine
import time
from time import gmtime, strftime
import pandas as pd 

####################################################
#   CONNECT TO MYSQL DB AND SELECT TEAMS TO QUERY  #
####################################################

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  
      
print('\n>>> Select 20 teams from MySQL DB where information is empty<<<\n')

missing_ids = []
team_data = []

with engine.connect() as con:
    sql_select = con.execute('SELECT id FROM `sm_teams` WHERE name IS NULL and short_code IS NULL and id != 0 LIMIT 20')
    for row in sql_select:
        missing_ids.append(row['id'])
   
print("Number of incomplete IDs: ", len(missing_ids))
print("Incomplete IDs: ", missing_ids)

########################################################
#   CONNECT TO API AND QUERY INFORMATION ON THE TEAMS  #
########################################################

print('\n>>> Query API with IDs <<<\n')

from sm_api_connection import sportmonks_token

if len(missing_ids) > 0:

    for single_id in missing_ids:

        print("API Query for missing team", str(single_id))
        api_response = requests.get("https://soccer.sportmonks.com/api/v2.0/teams/"+str(single_id)+"?api_token="+sportmonks_token)
        print("API response code: " + str(response.status_code))

        api_data = api_response.json()
        team = api_data['data']
        
        # collect data
        team_list = []
        team_list.append(team['id'])
        team_list.append(team['name'])
        team_list.append(team['short_code'])
        team_list.append(team['founded'])
        team_list.append(team['venue_id'])
        team_list.append(team['logo_path'])
        team_list.append(team['current_season_id'])    
        team_list.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))    
        team_data.append(team_list)

        time.sleep(5)
    
    # create final dataframe
    df_teams = pd.DataFrame(columns=['id','name','short_code','founded','venue_id','logo_path','current_season_id','load_ts'], data=team_data)

    print(df_teams.head())
    

    # Connect to database and update the missing entries with temp table

    print('\n>>> Updating MySQL DB <<<\n')
    
    try:
        df_teams.to_sql(name='tmp_sm_teams', con=engine, index=False, if_exists='replace')
        with engine.connect() as con:
            con.execute('''
                            UPDATE
                                sm_teams prd,
                                tmp_sm_teams tmp
                            SET
                                prd.name = tmp.name
                                , prd.short_code = tmp.short_code
                                , prd.founded = tmp.founded
                                , prd.logo_path = tmp.logo_path
                            WHERE
                                prd.id = tmp.id;  
                        ''')            
            con.execute('DROP TABLE tmp_sm_teams;')    
        
        db_message = "Success Updating teams table!"
        
    except:
        db_message = "Failed Updating teams table!"
    
    finally:
        con.close()
      
    print(db_message)
