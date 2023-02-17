#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Wed Jun 10 16:25:01 2020
@author: lennart

Updates the dimension tables for Bundesliga rounds (e.g. Spieltag 15) and fixtures (e.g. Schalke 04 vs. Eintracht Frankfurt)
    
"""

import sys
sys.path.insert(1, './secrets/')
import requests
import pandas as pd 
from datetime import datetime
import time
from time import gmtime, strftime
from sqlalchemy import create_engine
import sys

###############################
#   METAINFOS FROM MYSQL DB   #
###############################

print('\n>>> GET META-DATA FROM MYSQL DB<<<\n')

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

with engine.connect() as con:
    sql_select = con.execute('SELECT spieltag FROM parameter')
    sql_first_row = sql_select.fetchone()
    aktueller_fantasy_spieltag =  sql_first_row['spieltag']
    
    sql_select = con.execute('SELECT season_id FROM sm_seasons WHERE is_current_season = 1')
    sql_first_row = sql_select.fetchone()
    aktuelle_buli_season =  sql_first_row['season_id']
        
        
print("Current round in fantasy-game:", aktueller_fantasy_spieltag)
print("Current season-id in fantasy-game:", aktuelle_buli_season)

##################
#   GET ROUNDS   #
##################

from sm_api_connection import sportmonks_token
print("API Query for rounds and fixtures")
response = requests.get("https://soccer.sportmonks.com/api/v2.0/seasons/"+str(aktuelle_buli_season)+"?api_token="+my_token+"&tz=Europe/Amsterdam&include=rounds.fixtures,fixtures.round,fixtures.stats")
print("API response code: " + str(response.status_code))

data_spieltag = response.json()
data_spieltag = data_spieltag['data']['rounds']['data']

round_data = []

# Parse single rounds
for spieltag in data_spieltag:
    round_list = []
    
    round_list.append(spieltag['season_id'])
    round_list.append(spieltag['id'])
    round_list.append(spieltag['name'])
    
    if spieltag['start'] is not None:
        round_list.append(datetime.strptime(spieltag['start'], '%Y-%m-%d').date())
    else:
        round_list.append(None)
        
    if spieltag['end'] is not None:
        round_list.append(datetime.strptime(spieltag['end'], '%Y-%m-%d').date())
    else:
        round_list.append(None)
    
    all_fixtures_ft = True
    for match in spieltag['fixtures']['data']:
        if match['time']['status'] != 'FT':
            all_fixtures_ft = False
            
    round_list.append(all_fixtures_ft)
    round_list.append(True)    
    round_list.append(False)  
    round_list.append(True)    
    round_list.append(spieltag['league_id'])
    round_list.append(spieltag['stage_id'])

    round_list.append(strftime("%Y-%m-%d %H:%M:%S", time.localtime()))

    round_data.append(round_list)
           
df_rounds = pd.DataFrame(columns=['season_id', 'id', 'name', 'start_dt', 'end_dt', 'is_round_complete', 'is_fantasy_league_round', 'is_fantasy_cup_round', 'is_fantasy_active', 'league_id', 'stage_id', 'load_ts'], data=round_data)

##########################
#   WRITE INTO DATABASE  #
##########################

print('\n>>> WRITE INTO DATABASE <<<\n')

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# Create table if not exists
try:
    df_rounds.to_sql(name='sm_rounds', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_rounds` ADD PRIMARY KEY (`season_id`,`id`,`league_id`,`stage_id`);')
    message = 'Table sm_rounds created'

# If exists update table through temp table
except:
    df_rounds.to_sql(name='tmp_sm_rounds', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_rounds` ADD PRIMARY KEY (`season_id`,`id`,`league_id`,`stage_id`);')   
        con.execute('INSERT INTO sm_rounds SELECT * FROM tmp_sm_rounds t2 ON DUPLICATE KEY UPDATE start_dt = t2.start_dt, end_dt = t2.end_dt, is_round_complete = t2.is_round_complete;')    
        con.execute('DROP TABLE tmp_sm_rounds;')    
    message = "Table sm_rounds updated"

finally:
    con.close()
  
print(message)

####################
#   GET FIXTURES   #
####################

spieltage_mit_stats = []

# Define for which rounds fixtures should be extracted. If ran in command line there is an option to define specific rounds.

# If exectued in command line the user can enter a range of valid round names
if sys.stdin.isatty() is True:  
    print("Start Spieltag:\n")
    input_start = input()
    print("End Spieltag:\n")
    input_ende = input()
    
    alle_spieltage = []
    alle_spieltage.extend(range(1, 35))
    if int(input_start) in alle_spieltage and int(input_ende) in alle_spieltage and int(input_start) <= int(input_ende):
        spieltage_mit_stats = []
        spieltage_mit_stats.extend(range(int(input_start), int(input_ende)+1))
        print(spieltage_mit_stats)
    else:
        sys.exit("Keine gültige Eingabe!")
        
# If executed in Cronjob
elif sys.stdin.isatty() is False:
    spieltage_mit_stats.append(aktueller_fantasy_spieltag)
    print(spieltage_mit_stats)

data_match = response.json()
data_match = data_match['data']['fixtures']['data']

fixture_data = []

# Iterate through fixtures
for fixture in data_match:
    fixture_list = []
    
    fixture_list.append(fixture['season_id'])
    fixture_list.append(fixture['round_id'])
    fixture_list.append(fixture['round']['data']['name'])
    fixture_list.append(fixture['id'])
    
    if fixture['time']['starting_at']['date'] is not None:
        fixture_list.append(datetime.strptime(fixture['time']['starting_at']['date'], '%Y-%m-%d').date())
    else:
        fixture_list.append(None)
        
    if fixture['time']['starting_at']['date_time'] is not None:
        fixture_list.append(datetime.strptime(fixture['time']['starting_at']['date_time'], '%Y-%m-%d %H:%M:%S'))
    else:
        fixture_list.append(None)
        
    fixture_list.append(fixture['time']['status'])
    fixture_list.append(fixture['localteam_id'])
    fixture_list.append(fixture['visitorteam_id'])
    fixture_list.append(fixture['scores']['localteam_score'])
    fixture_list.append(fixture['scores']['visitorteam_score']) 
    fixture_list.append(fixture['league_id'])
    fixture_list.append(fixture['stage_id'])
    
    # If the fixture has more match stats, add them
    if fixture['round']['data']['name'] in spieltage_mit_stats:        
        fixture_list.append(fixture['time']['starting_at']['time']) 
        fixture_list.append(fixture['time']['starting_at']['timestamp'])
        fixture_list.append(fixture['time']['starting_at']['timezone'])   
        fixture_list.append(fixture['time']['added_time'])
        fixture_list.append(fixture['time']['extra_minute'])
        fixture_list.append(fixture['time']['injury_time'])
        fixture_list.append(fixture['time']['minute'])
        fixture_list.append(fixture['time']['second'])
        fixture_list.append(fixture['time']['status'])

        fixture_list.append(fixture['standings']['localteam_position'])
        fixture_list.append(fixture['standings']['visitorteam_position'])
    
        fixture_list.append(fixture['scores']['ft_score'])
        fixture_list.append(fixture['scores']['ht_score'])
                
        fixture_list.append(fixture['coaches']['localteam_coach_id'])
        fixture_list.append(fixture['coaches']['visitorteam_coach_id'])
                                        
        fixture_list.append(fixture['formations']['localteam_formation'])
        fixture_list.append(fixture['formations']['visitorteam_formation']) 
        
        # If the fixture has advanced match stats, add them
        if len(fixture['stats']['data']) >= 1:

            for iteration,team_stats in enumerate(fixture['stats']['data']):
                
                fixture_list_stats = []
                
                fixture_list_stats.append(team_stats['attacks']['attacks'])
                fixture_list_stats.append(team_stats['attacks']['dangerous_attacks'])
                fixture_list_stats.append(team_stats['ball_safe'])
                fixture_list_stats.append(team_stats['corners'])
                fixture_list_stats.append(team_stats['fouls'])
                fixture_list_stats.append(team_stats['free_kick'])
                fixture_list_stats.append(team_stats['goal_attempts'])
                fixture_list_stats.append(team_stats['goal_kick'])
                fixture_list_stats.append(team_stats['goals'])
                fixture_list_stats.append(team_stats['injuries'])
                fixture_list_stats.append(team_stats['offsides'])
                fixture_list_stats.append(team_stats['passes']['total'])
                fixture_list_stats.append(team_stats['passes']['accurate']) 
                
                                       
                if team_stats['passes']['total'] is None or team_stats['passes']['accurate'] is None:
                    fixture_list_stats.append(None)
                else:
                    fixture_list_stats.append(team_stats['passes']['total']-team_stats['passes']['accurate'])
                fixture_list_stats.append(team_stats['penalties'])
                fixture_list_stats.append(team_stats['possessiontime'])
                fixture_list_stats.append(team_stats['saves'])
                fixture_list_stats.append(team_stats['shots']['total'])
                fixture_list_stats.append(team_stats['shots']['ongoal'])
                fixture_list_stats.append(team_stats['shots']['offgoal'])
                fixture_list_stats.append(team_stats['shots']['outsidebox'])
                fixture_list_stats.append(team_stats['shots']['insidebox']) 
                
                if 'blocked' in team_stats['shots']:
                    fixture_list_stats.append(team_stats['shots']['blocked'])
                else:
                    fixture_list_stats.append(0)
                fixture_list_stats.append(team_stats['substitutions'])
                fixture_list_stats.append(team_stats['throw_in'])
                fixture_list_stats.append(team_stats['redcards'])
                fixture_list_stats.append(team_stats['yellowcards'])
                fixture_list_stats.append(team_stats['yellowredcards']) 
                                      
                fixture_list += fixture_list_stats
        else:
            fixture_list.extend([None] * 56)      
        
        fixture_list.append(fixture['venue_id'])  
        fixture_list.append(fixture['neutral_venue'])          
        fixture_list.append(fixture['attendance'])
        
        # Weather data
        if fixture['weather_report'] is None:
            fixture_list.extend([None] * 11)            
        else:
            fixture_list.append(fixture['weather_report']['clouds'])
            fixture_list.append(fixture['weather_report']['code'])
            fixture_list.append(fixture['weather_report']['type'])
            fixture_list.append(fixture['weather_report']['coordinates']['lat'])
            fixture_list.append(fixture['weather_report']['coordinates']['lon'])
            fixture_list.append(fixture['weather_report']['humidity'])
            fixture_list.append(fixture['weather_report']['icon'])
            fixture_list.append(fixture['weather_report']['pressure'])
            fixture_list.append(fixture['weather_report']['temperature_celcius']['temp'])
            fixture_list.append(fixture['weather_report']['wind']['speed'])
            fixture_list.append(fixture['weather_report']['wind']['degree'])    

        fixture_list.append(fixture['referee_id'])    
        fixture_list.append(fixture['assistants']['first_assistant_id'])
        fixture_list.append(fixture['assistants']['second_assistant_id'])
        fixture_list.append(fixture['assistants']['fourth_official_id'])

    else:
        fixture_list.extend([None] * 75) 

    fixture_data.append(fixture_list)

# set column names
columns_names_fixtures = []
columns_names_fixtures += ['season_id','round_id','round_name', 'fixture_id','kickoff_dt','kickoff_ts','fixture_status','localteam_id',
                           'visitorteam_id','localteam_score','visitorteam_score','league_id','stage_id']
columns_names_fixtures += ['kickoff_time','kickoff_unix','timezone','added_time',
                           'extra_minute', 'injury_time','minute','second','match_status',
                           'localteam_position', 'visitorteam_position','ft_score','ht_score',
                           'localteam_coach_id','visitorteam_coach_id', 'localteam_formation','visitorteam_formation']
     
  
columns_names_fixture_stats = []
columns_names_fixture_stats += ['attacks', 'dangerous_attacks','ball_safe','corners','fouls', 'free_kick',
                                'goal_attempts', 'goal_kick', 'goals', 'injuries', 'offsides', 'passes_total',
                                'passes_accurate', 'passes_inaccurate', 'penalties','possessiontime','saves',
                                'shots_total', 'shots_ongoal', 'shots_offgoal', 'shots_outsidebox', 'shots_insidebox', 
                                'shots_blocked', 'substitutions', 'throw_in', 'redcards', 'yellowcards', 'yellowredcards'] 
        
columns_names_fixture_stats_home = ['localteam_' + name for name in columns_names_fixture_stats]
columns_names_fixture_stats_away = ['visitorteam_' + name for name in columns_names_fixture_stats]        
columns_names_fixtures += columns_names_fixture_stats_home
columns_names_fixtures += columns_names_fixture_stats_away

columns_names_fixtures += ['venue_id', 'neutral_venue', 'attendance'] 
columns_names_fixtures += ['cloud_rate', 'weather_code', 'cloud_type', 'weather_lat', 'weather_lon', 'humidity', 'weather_icon',
                           'pressure', 'temperature','wind_speed','wind_degree'] 
columns_names_fixtures += ['referee_id', 'first_assistant_id', 'second_assistant_id', 'fourth_official_id']

# Create dataframe
df_fixtures = pd.DataFrame(columns=columns_names_fixtures, data=fixture_data)


##########################
#   WRITE INTO DATABASE  #
##########################

print('\n>>> WRITE INTO DATABASE <<<\n')

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)     

# Create table if not exists
try:
    df_fixtures.to_sql(name='sm_fixtures', con=engine, index=False, if_exists='fail')
    with engine.connect() as con:
        con.execute('ALTER TABLE `sm_fixtures` ADD PRIMARY KEY (`season_id`, `round_id`, `fixture_id`, `league_id`, `stage_id`);')
    message = 'Table sm_fixtures created'

# If exists update table through temp table
except:
    df_fixtures.to_sql(name='tmp_sm_fixtures', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        con.execute('ALTER TABLE `tmp_sm_fixtures` ADD PRIMARY KEY (`season_id`, `round_id`, `fixture_id`, `league_id`, `stage_id`);')   
        con.execute("""
                    INSERT INTO sm_fixtures 
                    SELECT * FROM tmp_sm_fixtures t2 
                        ON DUPLICATE KEY UPDATE 
                        kickoff_dt = t2.kickoff_dt
                        , kickoff_ts = t2.kickoff_ts
                        , fixture_status = t2.fixture_status
                        , localteam_id = t2.localteam_id
                        , visitorteam_id = t2.visitorteam_id
                        , localteam_score=t2.localteam_score
                        , visitorteam_score=t2.visitorteam_score 
                        , kickoff_time=t2.kickoff_time
                        , kickoff_unix=t2.kickoff_unix
                        , timezone=t2.timezone
                        , added_time=t2.added_time
                        , extra_minute=t2.extra_minute
                        , injury_time=t2.injury_time
                        , minute=t2.minute
                        , second=t2.second
                        , match_status=t2.match_status
                        , localteam_position=t2.localteam_position
                        , visitorteam_position=t2.visitorteam_position
                        , ft_score=t2.ft_score
                        , ht_score=t2.ht_score
                        , localteam_coach_id=t2.localteam_coach_id
                        , visitorteam_coach_id=t2.visitorteam_coach_id
                        , localteam_formation=t2.localteam_formation
                        , visitorteam_formation=t2.visitorteam_formation
                        
                        , localteam_attacks=t2.localteam_attacks
                        , localteam_dangerous_attacks=t2.localteam_dangerous_attacks
                        , localteam_ball_safe=t2.localteam_ball_safe
                        , localteam_corners=t2.localteam_corners
                        , localteam_fouls=t2.localteam_fouls
                        , localteam_free_kick=t2.localteam_free_kick
                        , localteam_goal_attempts=t2.localteam_goal_attempts
                        , localteam_goal_kick=t2.localteam_goal_kick
                        , localteam_goals=t2.localteam_goals
                        , localteam_injuries=t2.localteam_injuries
                        , localteam_offsides=t2.localteam_offsides
                        , localteam_passes_total=t2.localteam_passes_total
                        , localteam_passes_accurate=t2.localteam_passes_accurate
                        , localteam_passes_inaccurate=t2.localteam_passes_inaccurate
                        , localteam_penalties = t2.localteam_penalties
                        , localteam_possessiontime = t2.localteam_possessiontime
                        , localteam_saves = t2.localteam_saves
                        , localteam_shots_total = t2.localteam_shots_total
                        , localteam_shots_ongoal = t2.localteam_shots_ongoal
                        , localteam_shots_offgoal = t2.localteam_shots_offgoal
                        , localteam_shots_outsidebox = t2.localteam_shots_outsidebox
                        , localteam_shots_insidebox = t2.localteam_shots_insidebox
                        , localteam_shots_blocked = t2.localteam_shots_blocked
                        , localteam_substitutions = t2.localteam_substitutions
                        , localteam_throw_in = t2.localteam_throw_in
                        , localteam_redcards = t2.localteam_redcards
                        , localteam_yellowcards = t2.localteam_yellowcards
                        , localteam_yellowredcards = t2.localteam_yellowredcards
                        
                        , visitorteam_attacks=t2.visitorteam_attacks
                        , visitorteam_dangerous_attacks=t2.visitorteam_dangerous_attacks
                        , visitorteam_ball_safe=t2.visitorteam_ball_safe
                        , visitorteam_corners=t2.visitorteam_corners
                        , visitorteam_fouls=t2.visitorteam_fouls
                        , visitorteam_free_kick=t2.visitorteam_free_kick
                        , visitorteam_goal_attempts=t2.visitorteam_goal_attempts
                        , visitorteam_goal_kick=t2.visitorteam_goal_kick
                        , visitorteam_goals=t2.visitorteam_goals
                        , visitorteam_injuries=t2.visitorteam_injuries
                        , visitorteam_offsides=t2.visitorteam_offsides
                        , visitorteam_passes_total=t2.visitorteam_passes_total
                        , visitorteam_passes_accurate=t2.visitorteam_passes_accurate
                        , visitorteam_passes_inaccurate=t2.visitorteam_passes_inaccurate
                        , visitorteam_penalties = t2.visitorteam_penalties
                        , visitorteam_possessiontime = t2.visitorteam_possessiontime
                        , visitorteam_saves = t2.visitorteam_saves
                        , visitorteam_shots_total = t2.visitorteam_shots_total
                        , visitorteam_shots_ongoal = t2.visitorteam_shots_ongoal
                        , visitorteam_shots_offgoal = t2.visitorteam_shots_offgoal
                        , visitorteam_shots_outsidebox = t2.visitorteam_shots_outsidebox
                        , visitorteam_shots_insidebox = t2.visitorteam_shots_insidebox
                        , visitorteam_shots_blocked = t2.visitorteam_shots_blocked
                        , visitorteam_substitutions = t2.visitorteam_substitutions
                        , visitorteam_throw_in = t2.visitorteam_throw_in
                        , visitorteam_redcards = t2.visitorteam_redcards
                        , visitorteam_yellowcards = t2.visitorteam_yellowcards
                        , visitorteam_yellowredcards = t2.visitorteam_yellowredcards
                        
                        , venue_id = t2.venue_id
                        , neutral_venue = t2.neutral_venue
                        , attendance = t2.attendance
                        , cloud_rate = t2.cloud_rate
                        , weather_code = t2.weather_code
                        , cloud_type = t2.cloud_type
                        , weather_lat = t2.weather_lat
                        , weather_lon = t2.weather_lon
                        , humidity = t2.humidity
                        , weather_icon = t2.weather_icon
                        , pressure = t2.pressure
                        , temperature = t2.temperature
                        , wind_speed = t2.wind_speed
                        , wind_degree = t2.wind_degree
                        
                        , referee_id = t2.referee_id
                        , first_assistant_id = t2.first_assistant_id
                        , second_assistant_id = t2.second_assistant_id
                        , fourth_official_id = t2.fourth_official_id
                    ;    
                    """)
        con.execute('DROP TABLE tmp_sm_fixtures;')    

    message = "Table sm_fixtures updated"

finally:
    con.close()
  
print(message)
