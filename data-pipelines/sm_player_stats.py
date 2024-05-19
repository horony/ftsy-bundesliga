 #!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Tue Jun  2 23:29:35 2020
@author: lennartteams

Script runs every 2 minutes to get player statistics (e.g. number of passes of Lewandowski) on current round.
Script has the option to be ran on command line and update a specific round.

"""

# load packages

#import json
import numpy as np
import pandas as pd 
from datetime import datetime
#import time
import pytz
from sqlalchemy import create_engine
import requests

# load custom functions and connections

import sys
sys.path.insert(1, '../secrets/')
from sm_api_connection import sportmonks_token

sys.path.insert(2, '../py/')
from logging_function import log, log_headline
from dataprep_functions import isNone

###############################
#   METAINFOS FROM MYSQL DB   #
###############################

log_headline('(1/5) GET FANTASY META-DATA FROM MYSQL DB')
log('Connecting to MySQL database')

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

with engine.connect() as con:
    sql_select = con.execute('SELECT spieltag FROM parameter')
    sql_first_row = sql_select.fetchone()
    aktueller_fantasy_spieltag =  sql_first_row['spieltag']
    
    sql_select = con.execute('SELECT season_id, season_name FROM parameter')
    sql_first_row = sql_select.fetchone()
    aktuelle_buli_season = sql_first_row['season_id']
    season_name = sql_first_row['season_name']
    
#aktueller_fantasy_spieltag = 2
#aktuelle_buli_season = 17361
#season_name = '2023/2024'

log("Current round-name in fantasy-game: " + str(aktueller_fantasy_spieltag))
log("Current season-id in fantasy-game: " + str(aktuelle_buli_season))
log("Current season-name in fantasy-game: " + str(season_name))
        
##################
#   GET ROUNDS   #
##################

log_headline("(2/5) GET ALL ROUNDS WITH FIXTURES FROM API")

log("Sending query to rounds endpoint")

response_round = requests.get(
    "https://api.sportmonks.com/v3/football/rounds/seasons/"
    + str(aktuelle_buli_season)
    + "?api_token="+sportmonks_token
    + "&include=fixtures"
    )

log("API response code: " + str(response_round.status_code))
log("Processing round and fixture data")

response_round = response_round.json()
data_round = response_round['data']

fixture_data = []
cnt_processed_rounds = 1

# iterate through rounds
for spieltag in data_round:
            
    # if positive: Loop through fixtures of the round
    log('Processing round ' + str(cnt_processed_rounds) + '/' + str(len(data_round)) + ' - round-name: ' + str(spieltag['name']))

    for matchup in spieltag['fixtures']:
        fixture_list = []
        
        # identifiers for season and round 
        fixture_list.append(int(matchup['season_id']))
        fixture_list.append(int(matchup['round_id']))
        
        fixture_list.append(int(spieltag['name']))
       
        fixture_list.append(matchup['id'])
        fixture_list.append(isNone(datetime.fromtimestamp(matchup['starting_at_timestamp'],pytz.timezone("Europe/Berlin")).date(),None))
        fixture_list.append(isNone(datetime.fromtimestamp(matchup['starting_at_timestamp'],pytz.timezone("Europe/Berlin")).strftime('%Y-%m-%d %H:%M:%S'),None))
        
        # add data to list
        fixture_data.append(fixture_list)
            
    cnt_processed_rounds = cnt_processed_rounds + 1
 
# safe results to DataFrame
df_fixtures = pd.DataFrame(columns=['season_id','round_id','round_name','fixture_id','start_dt','start_ts'], data=fixture_data)
log('Found ' + str(df_fixtures.shape[0]) + ' fixtures')

###########################################
#   A) Script excuted from command line   #
###########################################

fixture_ids_all = []

log_headline('(3/5) SELECTING FIXTURES TO UPDATE WITH PLAYER STATS')    

log('Script is executed from command line: ' + str(sys.stdin.isatty()))

if sys.stdin.isatty() is True:
    
    # Show user input menu
    print("Welcher Spieltag soll geupdated werden?\n")
    print("Optionen (Input = Beschreibung):\n")
    print("number e.g. 1 or 29 = exakter Spieltag | all | all_till_today")
    input_choice = input()

    # Option 1: Update all rounds
    if input_choice == 'all':
        print("Update alle Spieltage!")
        df_fixtures_selected = df_fixtures
        fixture_extract = df_fixtures_selected['fixture_id'].tolist()
        
    # Option 2: Update all rounds up till today
    elif input_choice == 'all_till_today':
        print("Update alle Spieltage bis inkl. " + str(aktueller_fantasy_spieltag) + "!")
        df_fixtures_selected = df_fixtures[df_fixtures['round_name'] <= int(aktueller_fantasy_spieltag)] 
        print(df_fixtures_selected)
        fixture_extract = df_fixtures_selected['fixture_id'].tolist()    
     
    # Option 3: Specific round name 
    elif int(input_choice) in list(range(1, 35)):
        print("Update Spieltag " + str(input_choice) + "!")
        df_fixtures_selected = df_fixtures[df_fixtures['round_name'] == int(input_choice)] 
        print(df_fixtures_selected)
        fixture_extract = df_fixtures_selected['fixture_id'].tolist()
    
    else:
        sys.exit("Keine gültige Eingabe!")
        
    # Display how many fixtures will be updated
    log('Selected ' + str(len(df_fixtures_selected)) + ' fixtures')

####################################
#   B) Script excuted in cronjob   #
####################################

if sys.stdin.isatty() is False:
    
    log('Filtering df_fixtures for round_name = ' + str(aktueller_fantasy_spieltag))
    df_fixtures_selected = df_fixtures[df_fixtures['round_name'] == int(aktueller_fantasy_spieltag)] 
    log('Selected ' + str(len(df_fixtures_selected)) + ' fixtures')
        
    fixture_extract = df_fixtures_selected['fixture_id'].tolist()

fixture_ids_all = fixture_extract

# Split the fixtures defined previously in batches of 9
fixture_ids_collection = []

log('Creating batches from selected fixtures')

while len(fixture_ids_all) > 0:
    fixture_ids_collection.append(fixture_ids_all[0:9])    
    del fixture_ids_all[0:9]

log("Created " + str(len(fixture_ids_collection)) + " batch")

if len(fixture_ids_collection) == 0:
    log("ERROR - 0 batches created")
    quit()

#################################
#   GET PLAYER STATS FROM API   #
#################################

log_headline('(4/X5 GET PLAYER STATS FROM API')    

player_list = []
stat_list = []
fixture_list = []

for spieltag in fixture_ids_collection:
    
    # Get fixtures from batch
    fixture_ids_str =   ','.join(str(x) for x in spieltag)
       
    log("Sending query to fixtures endpoint")

    response = requests.get(
        "https://api.sportmonks.com/v3/football/fixtures/multi/"
        + fixture_ids_str
        + "?api_token="+sportmonks_token
        + "&include=scores;round;lineups.details.type;state"
        )

    log("API response code: " + str(response.status_code))
    log("Processing fixture data")

    data = response.json()['data']   
       
    # extract meta information from fixture
    for match in data:
    
        # players can be in the lineup (prev also in benched or not active)
        fixture_lineup = match['lineups']
        fixture_active = fixture_lineup

        # meta
        round_id = int(match['round_id'])    
        round_name = int(match['round']['name'])    
        
        localteam_id = None
        localteam_score = None
        visitorteam_id = None
        visitorteam_score = None
        
        for score in match['scores']:
            if score['type_id'] == 1525 and score['score']['participant'] == 'home':
                localteam_id = int(score['participant_id'])
                localteam_score = int(score['score']['goals'])
            if score['type_id'] == 1525 and score['score']['participant'] == 'away':
                visitorteam_id = int(score['participant_id'])
                visitorteam_score = int(score['score']['goals'])
        
        fixture_status = match['state']['short_name']
        fixture_kickoff_ts = datetime.fromtimestamp(match['starting_at_timestamp'])
        fixture_kickoff_dt = fixture_kickoff_ts.date()

        # parsing players
        for player in fixture_active:
            
            player_data = []  
            
            # player_id
            player_data.append(player['player_id'])
            
            # player_name
            player_data.append(player['player_name'])
            
            # dates
            player_data.append(player['fixture_id'])
            player_data.append(fixture_kickoff_ts)
            player_data.append(fixture_kickoff_dt) 
                      
            # season, round and fixture data     
            player_data.append(aktuelle_buli_season)
            player_data.append(season_name)
            player_data.append(round_id)
            player_data.append(round_name)
            
            player_data.append(fixture_status)
            
            # team_ids and team_names
            player_data.append(localteam_id)
            player_data.append(visitorteam_id)
            player_data.append(player['team_id'])
            
            if player['team_id'] == visitorteam_id:
                player_data.append(localteam_id)
                player_data.append('away')
            else:
                player_data.append(visitorteam_id)
                player_data.append('home')       
                
            # fixture type: Define if home game of away game
            if player['team_id'] == visitorteam_id:
                team_goals_scored = visitorteam_score
                team_goals_conceded = localteam_score
            else:
                team_goals_scored = localteam_score
                team_goals_conceded = visitorteam_score
                
            player_data.append(team_goals_scored)
            player_data.append(team_goals_conceded)  

            # jersey number
            player_data.append(player['jersey_number'])
            
            # player_active_flg: Lineup or bench
            if player['type_id'] == 11 or player['type_id'] == 12:
                player_data.append(1)
            else:
                player_data.append(0)
            
            # parse stats details
            for stat in player['details']:
                stats_data = []
                
                stats_data.append(int(player['player_id']))
                stats_data.append(int(player['fixture_id']))
                stats_data.append(round_name)
                stats_data.append(player['player_name'])
                stats_data.append(int(stat['type_id']))
                stats_data.append(stat['type']['code'])
                stats_data.append(isNone(int(stat['data']['value']),0))
                
                stat_list.append(stats_data)
            
            player_list.append(player_data)
            
df_player_stats = pd.DataFrame(
    data=player_list
    , columns =  [
        'player_id'
        , 'player_name'
        , 'fixture_id'
        , 'fixture_kickoff_ts'
        , 'fixture_kickoff_dt'
        , 'season_id'
        , 'season_name'
        , 'round_id'
        , 'round_name'
        , 'fixture_status'
        , 'localteam_id'
        , 'visitorteam_id'
        , 'own_team_id'
        , 'opp_team_id'
        , 'fixture_type'
        , 'team_goals_scored'
        , 'team_goals_conceded'
        , 'number'
        , 'active_flg'
        ]
    )

df_stats = pd.DataFrame(
    data=stat_list
    ,  columns=['player_id','fixture_id','round_name','player_name','stat_type_id','stat_code','stat_value']
    )
log('Pivot stat data')

df_stats = df_stats.pivot_table(
    index=["player_id", "fixture_id", "round_name","player_name"]
    , columns='stat_code'
    , values='stat_value'
    , aggfunc='first'
    ).reset_index()

def calc_ftsy_stat(calc_df,target_column,calc_column_a,calc_column_b,math_operation):
    if calc_column_b in calc_df.columns and calc_column_b in calc_df.columns:
        if calc_df[calc_column_a] is None and calc_df[calc_column_b] is None:
            calc_df[target_column] = None
        else:
            if math_operation == 'diff':
                calc_df[target_column] = isNone(calc_df[calc_column_a],0) - isNone(calc_df[calc_column_b],0)
            else:
                calc_df[target_column] = None
    else:
        calc_df[target_column] = None
     
    return calc_df[target_column]

df_stats = df_stats.rename(columns={
    'accurate-crosses': 'crosses_complete'
    , 'accurate-passes': 'passes_complete'
    , 'accurate-passes-percentage': 'passes_accuracy'
    , 'big-chances-created': 'big_chances_created' #new
    , 'big-chances-missed': 'big_chances_missed' #new
    , 'blocked-shots': 'blocks' 
    , 'clearance-offline': 'clearance_offline' #new
    , 'dribble-attempts': 'dribble_attempts'
    , 'dribbled-past': 'dribbled_past'
    , 'duels-lost': 'duels_lost'
    , 'duels-won': 'duels_won'
    , 'error-lead-to-goal': 'error_lead_to_goal' #new
    , 'fouls': 'fouls_committed'
    , 'fouls-drawn': 'fouls_drawn'
    , 'goalkeeper-goals-conceded': 'goalkeeper_goals_conceded' #new 
    , 'goals': 'goals_total'
    , 'goals-conceded': 'goals_conceded'
    , 'hit-woodwork':  'hit_woodwork'
    , 'key-passes': 'key_passes'
    , 'minutes-played': 'minutes_played'
    , 'own-goals': 'owngoals'
    , 'passes': 'passes_total'
    , 'penalties-committed': 'pen_committed'
    , 'penalties-missed': 'pen_missed'
    , 'penalties-saved':  'pen_saved'
    , 'penalties-scored': 'pen_scored'
    , 'penalties-won': 'pen_won'
    , 'saves-insidebox': 'inside_box_saves'
    , 'shots-blocked': 'shots_blocked'
    , 'shots-off-target': 'shots_missed'
    , 'shots-on-target': 'shots_on_goal'
    , 'shots-total': 'shots_total'
    , 'successful-dribbles': 'dribbles_success'
    , 'through-balls': 'through_balls'
    , 'through-balls-won': 'through_balls_won'
    , 'total-crosses': 'crosses_total'
    , 'total-duels': 'duels_total'
    , 'yellowred-cards': 'redyellowcards'
    })

if 'pen_scored' not in df_stats.columns:
        df_stats['pen_scored'] = 0
        
if 'goals_total' not in df_stats.columns:
        df_stats['goals_total'] = 0

if 'dribbles_failed' not in df_stats.columns:
        df_stats['dribbles_failed'] = 0

if 'dribble_attempts' not in df_stats.columns:
        df_stats['dribble_attempts'] = 0

if 'dribbles_success' not in df_stats.columns:
        df_stats['dribbles_success'] = 0
 
df_stats = df_stats.fillna(0)

df_stats['goals_minus_pen'] = isNone(df_stats['goals_total'],0) - isNone(df_stats['pen_scored'],0)
# calc_ftsy_stat(df_stats,'goals_minus_pen','goals_total','pen_scored','diff') 
df_stats['dribbles_failed'] = calc_ftsy_stat(df_stats,'dribbles_failed','dribble_attempts','dribbles_success','diff') 
df_stats['passes_incomplete'] = calc_ftsy_stat(df_stats,'passes_incomplete','passes_total','passes_complete','diff') 
df_stats['crosses_incomplete'] = calc_ftsy_stat(df_stats,'crosses_incomplete','crosses_total','crosses_complete','diff') 
df_stats['outside_box_saves'] = calc_ftsy_stat(df_stats,'outside_box_saves','saves','inside_box_saves','diff') 

cols_to_check = [
    'appearance'
    , 'clean_sheet'
    , 'goals_total'
    , 'goals_minus_pen'
    , 'assists'
    , 'goals_conceded'
    , 'owngoals'
    , 'yellowcards'
    , 'redcards'
    , 'yellowredcards'
    , 'dribble_attempts'
    , 'dribbles_success'
    , 'dribbles_failed'
    , 'dribbled_past'
    , 'duels_total'
    , 'duels_won'
    , 'duels_lost'
    , 'fouls_drawn'
    , 'fouls_committed'
    , 'shots_total'
    , 'shots_on_goal'
    , 'shots_missed'
    , 'crosses_total'
    , 'crosses_complete'
    , 'crosses_incomplete'
    , 'passes_complete'
    , 'passes_total'
    , 'passes_incomplete'
    , 'passes_accuracy'
    , 'key_passes'
    , 'blocks'
    , 'clearances'
    , 'dispossessed'
    , 'hit_woodwork'
    , 'inside_box_saves'
    , 'interceptions'
    , 'minutes_played'
    , 'offsides'
    , 'pen_committed'
    , 'pen_missed'
    , 'pen_saved'
    , 'pen_scored'
    , 'pen_won'
    , 'redyellowcards'
    , 'saves'
    , 'tackles'
    , 'outside_box_saves'
    # new columns
    , 'big_chances_created'
    , 'big_chances_missed'
    , 'clearance_offline'
    , 'error_lead_to_goal'
    , 'goalkeeper_goals_conceded'
    , 'shots_blocked'
    , 'punches'
    ]

df_stats = df_stats.assign(**{col : 0 for col in np.setdiff1d(cols_to_check,df_stats.columns.values)})

df_stats.loc[df_stats['minutes_played'] > 0,'appearance'] = 1

df_stats.loc[(df_stats['minutes_played'] >= 10) & (df_stats['goals_conceded']==0),'clean_sheet'] = 1

df_stats.loc[(df_stats['redcards'] > 1),'redcards'] = 1

df_stats.loc[(df_stats['redyellowcards'] > 1),'redyellowcards'] = 1
df_stats.loc[(df_stats['redyellowcards'] == 1),'redcards'] = 0

df_stats = df_stats[[
    'player_id'
    , 'fixture_id'
    # playing time
    , 'appearance'
    , 'minutes_played'
    # scoring
    , 'goals_total'
    , 'goals_minus_pen'
    , 'assists'
    # passing
    , 'big_chances_created'
    , 'key_passes'
    , 'passes_total'
    , 'passes_complete'
    , 'passes_incomplete'
    , 'passes_accuracy'    
    , 'crosses_total'
    , 'crosses_complete'
    , 'crosses_incomplete'  
    # shots
    , 'shots_total'  
    , 'shots_on_goal'
    , 'shots_missed'
    , 'shots_blocked'
    , 'big_chances_missed' 
    , 'hit_woodwork' 
    # penalties
    , 'pen_committed'
    , 'pen_missed'
    , 'pen_saved'
    , 'pen_scored'
    , 'pen_won'
    # duels
    , 'duels_total'
    , 'duels_won'
    , 'duels_lost'    
    , 'dribble_attempts'
    , 'dribbles_success'
    , 'dribbles_failed'
    # defense
    , 'clean_sheet'
    , 'goals_conceded'
    , 'goalkeeper_goals_conceded'
    , 'interceptions'
    , 'blocks'
    , 'clearances'
    , 'clearance_offline'
    , 'tackles'
    # errors
    , 'error_lead_to_goal'
    , 'owngoals'
    , 'dispossessed'        
    , 'dribbled_past'   
    # goalkeeper
    , 'saves'
    , 'inside_box_saves'
    , 'outside_box_saves'
    , 'punches'
    # cards
    , 'yellowcards'
    , 'redcards'
    , 'redyellowcards'   

    ]]

df_player_stats = pd.merge(df_player_stats, df_stats, how="outer", on=['fixture_id','player_id'])

##########################
#   WRITE INTO DATABASE  #
##########################

log_headline('(5/5) WRITE INTO DATABASE')
log('Connecting to MySQL database')    

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

log('Starting update process for sm_player_stats ')

log('Dropping tmp table tmp_sm_player_stats if still exists')
with engine.connect() as con:
    con.execute('DROP TABLE IF EXISTS tmp_sm_player_stats;')    

log('Creating tmp table tmp_sm_player_stats')
df_player_stats.to_sql(name='tmp_sm_player_stats', con=engine, index=False)


with engine.connect() as con:
    
    # set collation and primary key for tmp table
    log('Setting primary key for tmp_sm_player_stats to id')
    con.execute('ALTER TABLE `tmp_sm_player_stats` ADD PRIMARY KEY (`fixture_id`,`player_id`);')
    log('Setting collation for tmp_sm_player_stats to utf8mb4_unicode_520_ci')
    con.execute('ALTER TABLE `tmp_sm_player_stats` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;')    

    # updating prod table through tmp table
    log('Executing INSERT + UPDATE on sm_player_stats')    
    con.execute('''
                INSERT INTO sm_player_stats 
                SELECT  t2.*
                        , sysdate() as insert_ts
                        , null as update_ts
                        
                FROM tmp_sm_player_stats t2 
                
                ON DUPLICATE KEY UPDATE 
                    fixture_id = t2.fixture_id
                    , fixture_kickoff_ts = t2.fixture_kickoff_ts
                    , fixture_kickoff_dt = t2.fixture_kickoff_dt
                    , season_id = t2.season_id
                    , season_name = t2.season_name
                    , round_id = t2.round_id
                    , round_name = t2.round_name
                    , fixture_status = t2.fixture_status
                    , localteam_id = t2.localteam_id
                    , visitorteam_id = t2.visitorteam_id
                    , own_team_id = t2.own_team_id
                    , opp_team_id = t2.opp_team_id
                    , team_goals_scored = t2.team_goals_scored
                    , team_goals_conceded = t2.team_goals_conceded
                    , fixture_type = t2.fixture_type
                    , number = t2.number
                    , active_flg = t2.active_flg
                    , appearance = t2.appearance
                    , minutes_played = t2.minutes_played
                    , goals_total = t2.goals_total
                    , goals_minus_pen = t2.goals_minus_pen
                    , assists = t2.assists
                    , big_chances_created = t2.big_chances_created
                    , key_passes = t2.key_passes
                    , passes_total = t2.passes_total
                    , passes_complete = t2.passes_complete
                    , passes_incomplete = t2.passes_incomplete
                    , passes_accuracy = t2.passes_accuracy
                    , crosses_total = t2.crosses_total
                    , crosses_complete = t2.crosses_complete
                    , crosses_incomplete = t2.crosses_incomplete
                    , shots_total = t2.shots_total
                    , shots_on_goal = t2.shots_on_goal
                    , shots_missed = t2.shots_missed
                    , shots_blocked = t2.shots_blocked
                    , big_chances_missed = t2.big_chances_missed
                    , hit_woodwork = t2.hit_woodwork
                    , pen_committed = t2.pen_committed
                    , pen_missed = t2.pen_missed
                    , pen_saved = t2.pen_saved
                    , pen_scored = t2.pen_scored
                    , pen_won = t2.pen_won
                    , duels_total = t2.duels_total
                    , duels_won = t2.duels_won
                    , duels_lost = t2.duels_lost 
                    , dribble_attempts = t2.dribble_attempts
                    , dribbles_success = t2.dribbles_success
                    , dribbles_failed = t2.dribbles_failed
                    , clean_sheet = t2.clean_sheet
                    , goals_conceded = t2.goals_conceded
                    , goalkeeper_goals_conceded = t2.goalkeeper_goals_conceded
                    , interceptions = t2.interceptions
                    , blocks = t2.blocks
                    , clearances = t2.clearances
                    , clearance_offline = t2.clearance_offline
                    , tackles = t2.tackles
                    , error_lead_to_goal = t2.error_lead_to_goal
                    , owngoals = t2.owngoals
                    , dispossessed = t2.dispossessed
                    , dribbled_past = t2.dribbled_past
                    , saves = t2.saves
                    , inside_box_saves = t2.inside_box_saves
                    , outside_box_saves = t2.outside_box_saves
                    , punches = t2.punches
                    , yellowcards = t2.yellowcards
                    , redcards = t2.redcards
                    , redyellowcards = t2.redyellowcards
                    , update_ts = sysdate()
                ;
                ''')
                
    # drop tmp table as not needed anymore
    log('Dropping tmp table tmp_sm_player_stats')
    con.execute('DROP TABLE tmp_sm_player_stats;')    

log('Finished update process for sm_player_stats')

con.close()
