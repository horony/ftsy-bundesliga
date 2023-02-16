#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Tue Jul 23 23:15:19 2019

Connect to open source API openligadb and get Bundesliga matchdata.

@author: lennart
"""

import sys
sys.path.insert(1, './secrets/')
import requests
import pandas as pd
from sqlalchemy import create_engine

# Connect to API
r = requests.get('https://www.openligadb.de/api/getmatchdata/bl1')
data = (r.json())

# Setup dummy lists
home = []
away = []
score_home = []
score_away = []
status = []

# Get data for the nine fixtures of the current Bundesliga round
j = 0
while j <= 8:
    home.append(data[j]['Team1']['TeamName'])
    away.append(data[j]['Team2']['TeamName'])
    status.append(data[j]['MatchIsFinished'])
    if data[j]['Goals']:
       score_home.append(data[j]['Goals'][-1]['ScoreTeam1'])
       score_away.append(data[j]['Goals'][-1]['ScoreTeam2'])
    else:
        score_home.append(0)
        score_away.append(0)
    j=j+1

# Create dataframe from lists
df = pd.DataFrame()
df['home'] = home
df['away'] = away
df['tor_home'] = score_home
df['tor_away'] = score_away
df['status'] = status

# Code the full team names into abbreviations
dummy_list = ['home', 'away']
for i in dummy_list:
    df.loc[df[i] == 'FC Bayern', i]                 = 'FCB'
    df.loc[df[i] == 'Borussia Dortmund', i]         = 'BVB' 
    df.loc[df[i] == 'RB Leipzig', i]                = 'RBL' 
    df.loc[df[i] == 'Bayer Leverkusen', i]          = 'B04' 
    df.loc[df[i] == 'Borussia Mönchengladbach', i]  = 'BMG' 
    df.loc[df[i] == 'VfL Wolfsburg', i]             = 'VFL' 
    df.loc[df[i] == 'TSG 1899 Hoffenheim', i]       = 'TSG' 
    df.loc[df[i] == 'Eintracht Frankfurt' ,i]       = 'SGE' 
    df.loc[df[i] == 'Werder Bremen', i]             = 'SVW' 
    df.loc[df[i] == 'Fortuna Düsseldorf', i]        = 'F95' 
    df.loc[df[i] == 'Hertha BSC', i]                = 'BSC' 
    df.loc[df[i] == '1. FSV Mainz 05', i]           = 'FSV' 
    df.loc[df[i] == 'SC Freiburg', i]               = 'SCF' 
    df.loc[df[i] == 'FC Schalke 04', i]             = 'S04' 
    df.loc[df[i] == 'FC Augsburg', i]               = 'FCA' 
    df.loc[df[i] == '1. FC Köln', i]                = 'FCK' 
    df.loc[df[i] == 'SC Paderborn 07', i]           = 'SCP' 
    df.loc[df[i] == '1. FC Union Berlin', i]        = 'FCU' 

df.loc[df['status'] == False, 'status'] = 0
df.loc[df['status'] == True, 'status'] = 1

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)

# Create temp table tmp_opendb
df.to_sql(name='tmp_opendb', con=engine, if_exists='replace')

# 1.) Update the scoring fact table with the temp table
with engine.begin() as cn:
    sql = """UPDATE fantasy_scoring_akt, 
                    tmp_opendb
             SET    fantasy_scoring_akt.WW = 
                     CASE WHEN fantasy_scoring_akt.Gegner = tmp_opendb.home THEN
                         CASE WHEN tmp_opendb.tor_home > 0 THEN 0 ELSE 1 END
                          WHEN fantasy_scoring_akt.Gegner = tmp_opendb.away THEN
                         CASE WHEN tmp_opendb.tor_away > 0 THEN 0 ELSE 1 END
                     END                           
             WHERE  fantasy_scoring_akt.Gegner = tmp_opendb.home
                     OR fantasy_scoring_akt.Gegner = tmp_opendb.away
             """
    cn.execute(sql)
  
# 2.) Recalculate fantasy scores
with engine.begin() as cn:
    sql = """UPDATE fantasy_scoring_akt
             SET    fantasy_scoring_akt.`FB-Score` = 
                         fantasy_scoring_akt.`FB-Note` 
                         + COALESCE (fantasy_scoring_akt.Elfmeter, 0) * 3
                         + COALESCE (fantasy_scoring_akt.Assist, 0)
                         + COALESCE (fantasy_scoring_akt.Rot, 0) * -6
                         + COALESCE (fantasy_scoring_akt.`Gelb-Rot`,0) * -3
                         + CASE WHEN (fantasy_scoring_akt.Position_Fantasy = 'Tor' OR fantasy_scoring_akt.Position_Fantasy = 'Abwehr') AND fantasy_scoring_akt.WW = 1 AND fantasy_scoring_akt.Einsatz = 1 THEN 1 ELSE 0 END
                         +   CASE WHEN fantasy_scoring_akt.Position_Fantasy = 'Abwehr' THEN COALESCE(fantasy_scoring_akt.Tor,0) * 5
                                  WHEN fantasy_scoring_akt.Position_Fantasy = 'Mittelfeld' THEN COALESCE(fantasy_scoring_akt.Tor,0) * 4
                                  WHEN fantasy_scoring_akt.Position_Fantasy = 'Sturm' THEN COALESCE(fantasy_scoring_akt.Tor,0) * 3
                                  WHEN fantasy_scoring_akt.Position_Fantasy = 'Tor' THEN COALESCE(fantasy_scoring_akt.Tor,0) * 7
                                  ELSE 0 END
                                           
             """
    cn.execute(sql)
    
# 3.) Update schedule
with engine.begin() as cn:
    sql = """UPDATE bundesliga_spielplan, 
                    tmp_opendb
             SET    bundesliga_spielplan.tor_home = tmp_opendb.tor_home,
                    bundesliga_spielplan.tor_away = tmp_opendb.tor_away,
                    bundesliga_spielplan.status = tmp_opendb.status                         
             WHERE  bundesliga_spielplan.home = tmp_opendb.home
                    AND bundesliga_spielplan.away = tmp_opendb.away
                    AND time < NOW()
                    AND bundesliga_spielplan.status = 0
             
             """
    cn.execute(sql)



