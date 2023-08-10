#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Sun Jul 23 20:21:08 2023

@author: lennart
"""

import sys
sys.path.insert(1, '../secrets/')
from sm_api_connection import sportmonks_token

sys.path.insert(2, '../py/')
from logging_function import log, log_headline
from dataprep_functions import isNone

import requests
import pandas as pd 
from sqlalchemy import create_engine

response = requests.get(
    "https://api.sportmonks.com/v3/core/types"
    + "?api_token=" + sportmonks_token
    + "&filters=populate"
    )

log("API response code: " + str(response.status_code))

response_types = response.json()
data_types = response_types['data']

list_all_types = []
i = 1

for item in data_types:
    log(str(i)+'/'+str(len(data_types)))
    
    list_current_type = []

    list_current_type.append(item['id'])
    list_current_type.append(item['code'])
    list_current_type.append(item['developer_name'])
    list_current_type.append(item['model_type'])
    list_current_type.append(item['name'])
    list_current_type.append(isNone(item['stat_group'],None))
    list_current_type.append(0) # ftsy_stat_flg
    list_current_type.append(None) # german_display_name
    list_current_type.append(None) # update_ts
    list_current_type.append(None) # insert_ts


    list_all_types.append(list_current_type)
    i=i+1
    
df_types = pd.DataFrame(columns=['type_id','type_code','developer_name','model_type','name','stat_group','ftsy_stat_flg','german_display_name','update_ts','insert_ts'], data=list_all_types)
    
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
    df_types.to_sql(name='sm_types', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_types` ADD PRIMARY KEY (`type_id`);')
    db_message = 'Table sm_types created'

# if exists update table through temp table
except:
    df_types.to_sql(name='tmp_sm_types', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_types` ADD PRIMARY KEY (`type_id`);')   
        con.execute('''
                    INSERT INTO sm_types 
                    SELECT  t2.type_id
                            , t2.type_code
                            , t2.developer_name
                            , t2.model_type
                            , t2.name
                            , t2.stat_group
                            , 0 as ftsy_stat_flg
                            , null as german_display_name
                            , null as update_ts
                            , sysdate() as insert_ts
                            
                    FROM tmp_sm_types t2 
                    ON DUPLICATE KEY UPDATE 
                            type_id = t2.type_id
                            , type_code = t2.type_code
                            , developer_name = t2.developer_name
                            , name = t2.name
                            , stat_group = t2.stat_group
                            , update_ts = sysdate()
                    ;
                    ''')  
        con.execute('DROP TABLE tmp_sm_types;')    

    db_message = "Table sm_types updated"

finally:
    con.close()
  
log(db_message)

# german_display_name