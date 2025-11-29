#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Th Nov 27 21:13:21 2025

Script to orchestrate the player scoring predictions

@author: lennart
"""

import os
import sys
import subprocess

sys.path.insert(1, '../secrets/')
sys.path.insert(2, '../py/')
from logging_function import log

print("")
print("################################################")
print("#  RUNNING ORCHESTRATION FOR MACHINE LEARNING  #")
print("################################################")
print("")

# Ensure that rounds are up to date

print("")
print("##################")
print("#  SM_ROUNDS.PY  #")
print("##################")
print("")

log('Step 1/5 - Calling script sm_rounds.py')
os.system('python3.6 sm_rounds.py')

# Ensure that fixtures are up to date

print("")
print("####################")
print("#  SM_FIXTURES.PY  #")
print("####################")
print("")

os.system('python3.6 sm_fixtures.py')
log('Step 2/5 - Calling script sm_fixtures.py')

# Ensure that player are up to date

print("")
print("######################")
print("#  SM_PLAYERBASE.PY  #")
print("######################")
print("")

log('Step 3/5 - Calling script sm_playerbase.py')
os.system('python3.6 sm_playerbase.py')

print("")
print("#######################")
print("#  REFRESH AVG TABLE  #")
print("#######################")
print("")

log('Step 4/5 - Refreshing table ml_player_projection_by_avg ')

from sqlalchemy import create_engine
from mysql_db_connection import db_user, db_pass, db_port, db_name

engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# Save the avg projection into a table
with engine.connect() as con:

    # Create table if not exists
    con.execute('''
        CREATE TABLE IF NOT EXISTS ml_player_projection_by_avg AS
        SELECT 
            p.season_id 
            , p.spieltag AS round_name
            , v.player_id
            , v.ftsy_score_projected AS ftsy_score_projected_by_avg
            , SYSDATE() AS insert_ts
        FROM ftsy_scoring_projection_v v
        CROSS JOIN parameter p;
        ''')

    # Delete current records
    log('Deleting records from table ml_player_projection_by_avg')

    con.execute('''
        DELETE FROM ml_player_projection_by_avg 
        WHERE 
	        season_id = (SELECT season_id FROM parameter) 
            AND round_name = (SELECT spieltag FROM parameter)
        ;
        ''')
    
    # Insert refreshed current records
    log('Inserting records into table ml_player_projection_by_avg')

    con.execute('''
        INSERT INTO ml_player_projection_by_avg 
        SELECT 
            p.season_id 
            , p.spieltag AS round_name
            , v.player_id
            , v.ftsy_score_projected AS ftsy_score_projected_by_avg
            , SYSDATE() AS insert_ts
        FROM ftsy_scoring_projection_v v
        CROSS JOIN parameter p
        ;
        ''')

log('Update of table ml_player_projection_by_avg complete')

print("")
print("#####################################")
print("#  ML_PLAYER_MINUTES_PROJECTION.PY  #")
print("#####################################")
print("")

log('Step 4/5 - Calling script ml_player_minutes_projection')

venv_python = "../py/venv/venv-ml/bin/python"
subprocess.run([venv_python, "ml_player_minutes_projection.py"])

print("")
print("###################################")
print("#  ML_PLAYER_SCORE_PROJECTION.PY  #")
print("###################################")
print("")

log('Step 5/5 - Calling script ml_player_scores_projection')

venv_python = "../py/venv/venv-ml/bin/python"
subprocess.run([venv_python, "ml_player_score_projection.py"])