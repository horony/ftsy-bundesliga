#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Thu Oct  5 17:27:55 2023

@author: lennart

Creates and upadates three database tables containing top xis ('Elf der Woche') on different aggregations.

"""

# load packages

import pandas as pd
pd.options.mode.chained_assignment = None  
from sqlalchemy import create_engine, text, MetaData, Table

import sys
sys.path.insert(1, '../secrets/')

sys.path.insert(2, '../py/')
from logging_function import log, log_headline

# define custom functions

def aggregate_values(df_input, list_input_groupby):
    """
    Calculates ftsy_score_sum, ftsy_score_avg, appearance_cnt, appearance_mint_dt and appearance_max_dt
    """

    df = df_input 
    
    # calculate aggregations
    df = df.fillna(-1).groupby(list_groupby_values, as_index=False).agg({
        'ftsy_score': [('ftsy_score_sum', 'sum'), ('ftsy_score_avg', 'mean'), ('appearance_cnt', 'count')]
         ,'kickoff_dt': [('appearance_min_dt','min'),('appearance_max_dt','max')]
         }
    ).reset_index()
    
    # drop index columns
    df = df.droplevel(axis=1, level=1).reset_index().drop(columns=['level_0','index'])
    
    # rename last 5 columns
    list_columns = df.columns.tolist()
    list_columns[len(list_columns)-5:len(list_columns)] = ['ftsy_score','ftsy_score_avg','appearance_cnt','appearance_min_dt','appearance_max_dt']
    df.columns = list_columns
    
    # round values
    df["ftsy_score"] = df["ftsy_score"].round(1) 
    df["ftsy_score_avg"] = df["ftsy_score_avg"].round(1) 
    
    # replace -1 values from groupby
    df['user_id'] = df['user_id'].replace(-1, None)
    df['user_name'] = df['user_name'].replace(-1, None)
    df['user_team_name'] = df['user_team_name'].replace(-1, None)
    df['user_team_code'] = df['user_team_code'].replace(-1, None)
    df['user_team_logo_path'] = df['user_team_logo_path'].replace(-1, None)

    return df

    del df, list_columns

def filter_for_candidates(df_input):
    
    """
    Function filters an input DataFrame containing Bundesliga players for top xi candidates. 
    Thus only players which are ranked 5 or better on their positions are kept.
    Results are returned in a reduced DataFrame.
    """
    
    df_filter = pd.DataFrame()
    df_filter = df_input
    
    # Keep max of 5 players per position, as this the max possible value in the top xi
    df_filter = df_filter.loc[df_filter['rank'] <= 5] 
    
    # Keep max of 1 goalkeeper, as this the max possible value in the top xi
    df_filter = df_filter.drop(df_filter[(df_filter['rank'] != 1) & (df_filter['position_short'] == 'TW')].index)

    # Keep max of 3 attackers, as this the max possible value in the top xi
    df_filter = df_filter.drop(df_filter[(df_filter['rank'] > 3) & (df_filter['position_short'] == 'ST')].index)
    
    log('Filtered DataFrame from ' + str(len(df_input)) + ' players to ' + str(len(df_filter)) + ' players') 

    return df_filter

def calc_ftsy_scores_for_all_formations(df_input_scores, list_agg_cols):
    
    """
    Function takes an input DataFrame with Bundesliga players and their fantasy scores.
    The fantasy score are summarized for each possible formation (e.g 442, 541, etc) and added to the DataFrame.
    """
    
    df_formation_scores = pd.DataFrame()
    df_formation_scores = df_input_scores
    
    log('Aggregate fantasy scores by ' + str(list_agg_cols))
        
    # 343
    df_formation_scores['score_343'] = df_formation_scores.loc[(
        ((df_formation_scores['rank'] <= 4) & (df_formation_scores['position_short'].isin(['TW','MF'])))
        | ((df_formation_scores['rank'] <= 3) & (df_formation_scores['position_short'].isin(['ST','AW'])))
        )].groupby(list_agg_cols)['ftsy_score'].transform('sum')

    # 352
    df_formation_scores['score_352'] = df_formation_scores.loc[(
        ((df_formation_scores['rank'] <= 5) & (df_formation_scores['position_short'].isin(['TW','MF'])))
        | ((df_formation_scores['rank'] <= 3) & (df_formation_scores['position_short'].isin(['AW'])))
        | ((df_formation_scores['rank'] <= 2) & (df_formation_scores['position_short'].isin(['ST'])))
        )].groupby(list_agg_cols)['ftsy_score'].transform('sum')

    # 433
    df_formation_scores['score_433'] = df_formation_scores.loc[(
        ((df_formation_scores['rank'] <= 4) & (df_formation_scores['position_short'].isin(['TW','AW']))) 
        | ((df_formation_scores['rank'] <= 3) & (df_formation_scores['position_short'].isin(['ST','MF'])))
        )].groupby(list_agg_cols)['ftsy_score'].transform('sum')

    # 442
    df_formation_scores['score_442'] = df_formation_scores.loc[(
        ((df_formation_scores['rank'] <= 4) & (df_formation_scores['position_short'].isin(['TW','AW','MF']))) 
        | ((df_formation_scores['rank'] <= 2) & (df_formation_scores['position_short'] == 'ST'))
        )].groupby(list_agg_cols)['ftsy_score'].transform('sum')

    # 451
    df_formation_scores['score_451'] = df_formation_scores.loc[(
        ((df_formation_scores['rank'] <= 5) & (df_formation_scores['position_short'].isin(['TW','MF']))) 
        | ((df_formation_scores['rank'] <= 4) & (df_formation_scores['position_short'].isin(['AW'])))
        | ((df_formation_scores['rank'] <= 1) & (df_formation_scores['position_short'].isin(['ST'])))
        )].groupby(list_agg_cols)['ftsy_score'].transform('sum')

    # 532
    df_formation_scores['score_532'] = df_formation_scores.loc[(
        ((df_formation_scores['rank'] <= 5) & (df_formation_scores['position_short'].isin(['TW','AW']))) 
        | ((df_formation_scores['rank'] <= 3) & (df_formation_scores['position_short'].isin(['MF'])))
        | ((df_formation_scores['rank'] <= 2) & (df_formation_scores['position_short'].isin(['ST'])))    
        )].groupby(list_agg_cols)['ftsy_score'].transform('sum')

    # 541
    df_formation_scores['score_541'] = df_formation_scores.loc[(
        ((df_formation_scores['rank'] <= 5) & (df_formation_scores['position_short'].isin(['TW','AW']))) 
        | ((df_formation_scores['rank'] <= 4) & (df_formation_scores['position_short'].isin(['MF'])))
        | ((df_formation_scores['rank'] <= 1) & (df_formation_scores['position_short'].isin(['ST'])))    
        )].groupby(list_agg_cols)['ftsy_score'].transform('sum')
    
    log('Calculated fantasy scores for all possible formations')
    
    del list_agg_cols
    
    return df_formation_scores

def quality_check_formations(df_input_qc, list_agg_cols_qc):
    
    """
    Function to check if the selected players of a top xi match the formation of the top xi and adjust accordingly.
    This can happen if to formations score the same ftsy_points.
    """
    
    log('Check if calculated formation and number of players match')

    # load data
    df_qc = df_input_qc
    
    # extract allowed values (e.g 541 allows one ST) from the column 'formation'
    df_qc['formation_tw'] = 1
    df_qc['formation_aw'] = df_qc['formation'].astype(str).str[0].astype(int)
    df_qc['formation_mf'] = df_qc['formation'].astype(str).str[1].astype(int)
    df_qc['formation_st'] = df_qc['formation'].astype(str).str[2].astype(int)
    df_qc['formation'] = df_qc['formation'].astype(int)

    # add the allowed values to the respective players
    df_qc['qc_formation_allowed'] = None
    
    df_qc.loc[df_qc['position_short'] == 'TW', 'qc_formation_allowed'] = df_qc['formation_tw']
    df_qc.loc[df_qc['position_short'] == 'AW', 'qc_formation_allowed'] = df_qc['formation_aw']
    df_qc.loc[df_qc['position_short'] == 'MF', 'qc_formation_allowed'] = df_qc['formation_mf']
    df_qc.loc[df_qc['position_short'] == 'ST', 'qc_formation_allowed'] = df_qc['formation_st']
    
    # calculate the ranks to filter to check if allowed value is exceeded and delete those values
    df_qc["qc_rank"] = df_qc.groupby(list_agg_cols_qc)["ftsy_score"].rank(method="first", ascending=False)   
    log('Found ' + str(len(df_qc.loc[df_qc['qc_rank'] > df_qc['qc_formation_allowed']])) + ' issues')
    df_qc = df_qc.loc[df_qc['qc_rank'] <= df_qc['qc_formation_allowed']] 
    
    # cleanup
    df_qc = df_qc.drop(columns=['formation_tw', 'formation_aw', 'formation_mf', 'formation_st', 'qc_formation_allowed', 'qc_rank'])

    return df_qc

    del df_qc
    del list_agg_cols_qc
    
    
def write_to_database(source_dataframe, db_engine, table_name, create_stmt_path):
    """
    This functions drops a specific table on the MySQL DB and rewrites it from a DataFrame.
    - source_dataframe = Pandas DataFrame which should be written to the DB
    - db_engine = DB engine to connect to the DB
    - table_name = DB table which needs to be dropped and recreated
    - create_stmt_path = Path to the .sql file which creates the table_name
    """
    
    try:
        with db_engine.connect() as con:
            
            log('Connection to database established')
            
            # drop table if exists
            table_metadata = MetaData(db_engine)
            table_to_drop = Table(table_name, table_metadata)
            table_to_drop.drop(db_engine, checkfirst=True)
            log(str('Table ' + str(table_name) + ' dropped'))
            
            # create empty table
            with open(create_stmt_path) as file:
                stmt = text(file.read())
                con.execute(stmt)
            log(str('Table ' + str(table_name) + ' created with file ' + str(create_stmt_path)))
                
            # insert data into table
            source_dataframe.to_sql(table_name, db_engine, if_exists='append', index=False)
            log(str('Insert into table ' + str(table_name) + ' from DataFrame complete'))

            con.close()
            log('Connection to database closed')
    
    except:
        raise Exception('Error recreating and updating DB table')
        con.close()
        sys.exit()
    
##############################
#   GET DATA FROM MYSQL DB   #
##############################

log_headline('(1/5) COLLECTING DATA FROM MYSQL DB')
log('Connecting to MySQL database')

# connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

sql_select_stmt = '''
    SELECT  hst.season_id
            , hst.season_name
            , hst.round_name
            , hst.kickoff_dt
            , hst.player_id
            , pb.display_name as player_name
            , pb.image_path as player_image_path
            , hst.position_short
            , hst.current_team_id as buli_team_id
            , tm.short_code as buli_team_code
            , tm.name as buli_team_name
            , tm.logo_path as buli_team_logo_path
            , hst.ftsy_score
            , hst.1_ftsy_owner_id as user_id 
            , usr.username as user_name
            , usr.teamname as user_team_name
            , usr.team_code as user_team_code
            , null as user_team_logo_path
            , 1_ftsy_match_status

    FROM    ftsy_scoring_hist hst
    
    LEFT JOIN sm_teams tm
    	ON 	hst.current_team_id = tm.id
        
    LEFT JOIN users usr
    	ON  hst.1_ftsy_owner_id = usr.id
        
    LEFT JOIN sm_playerbase pb
        ON  hst.player_id = pb.id

    WHERE   hst.ftsy_score IS NOT NULL
            AND hst.position_short IS NOT NULL 
            AND hst.season_id > 17361 # season 20/21 has faulty data, see Haaland
            AND (hst.appearance_stat = 1 OR hst.ftsy_score != 0)             
    '''
    
df_data = pd.read_sql(sql_select_stmt, engine)
del sql_select_stmt
df_data['kickoff_dt'] = pd.to_datetime(df_data["kickoff_dt"]).dt.date

log('Loaded ' + str(len(df_data)) + ' rows into DataFrame df_data')

##################################
#   FANTASY BUNDESLIGA OVERALL   #
##################################

log_headline('(2/5) CALCULATING FANTASY BUNDESLIGA TOP XIs')

# define target structure of database table
list_fabu_column_names = [
    'topxi_lvl'
    ,'season_id'
    ,'season_name'
    ,'round_name'
    ,'formation'
    ,'position_short'
    ,'player_id'
    ,'player_name'
    ,'player_image_path'
    ,'buli_team_id'
    ,'buli_team_code'
    ,'buli_team_name'
    ,'buli_team_logo_path'
    ,'user_id'
    ,'user_name'
    ,'user_team_name'
    ,'user_team_code'
    ,'user_team_logo_path'
    ,'ftsy_score'
    ,'ftsy_score_avg'
    ,'appearance_cnt'
    ,'appearance_min_dt'
    ,'appearance_max_dt'
    ]

#############################################
#   FANTASY BUNDESLIGA OVERALL - ALL TIME   #
#############################################

log_headline('(2.1/5) CALCULATING FANTASY BUNDESLIGA ALL-TIME')
df_data_fabu_ovr = df_data 
    
# per player: Calculate latest team and position for each season
df_data_fabu_ovr_tail = df_data_fabu_ovr.sort_values(['season_id','round_name']).groupby('player_id').tail(1)
list_groupby_values = ['player_id','player_name','player_image_path','position_short','buli_team_id','buli_team_name','buli_team_code','buli_team_logo_path','user_id','user_name','user_team_name','user_team_code','user_team_logo_path']
df_data_fabu_ovr_tail = df_data_fabu_ovr_tail[list_groupby_values]

df_data_fabu_ovr = df_data_fabu_ovr[['player_id','kickoff_dt', 'ftsy_score']]

df_data_fabu_ovr = pd.merge(
    df_data_fabu_ovr
    , df_data_fabu_ovr_tail
    , how = 'left'
    , left_on = ['player_id']
    , right_on = ['player_id']
    )

del df_data_fabu_ovr_tail

# aggregate data to 1 row per player
df_data_fabu_ovr = aggregate_values(df_data_fabu_ovr, list_groupby_values)

df_data_fabu_ovr["rank"] = df_data_fabu_ovr.groupby(["position_short"])["ftsy_score"].rank(method="first", ascending=False)   

# raw filter rows for possible topxi candidates 
df_data_fabu_ovr = filter_for_candidates(df_data_fabu_ovr)

# calc formation scores
df_data_fabu_ovr['dummy'] = 1
list_agg_cols = ['dummy']
df_data_fabu_ovr = calc_ftsy_scores_for_all_formations(df_data_fabu_ovr, list_agg_cols)
df_data_fabu_ovr = df_data_fabu_ovr.sort_values(by=['ftsy_score'], ascending=False)
del list_agg_cols

# starting to calculate the actual top xi
        
# define max score possible score
df_data_fabu_ovr['player_max'] = df_data_fabu_ovr[['score_343','score_352','score_433','score_442','score_451','score_532','score_541']].max(axis=1)
df_data_fabu_ovr['global_max'] = df_data_fabu_ovr['player_max'].max()

# safe the formation
max_possible_ftsy_score = df_data_fabu_ovr['global_max'].max()
comparison_col = (df_data_fabu_ovr.columns[(df_data_fabu_ovr==max_possible_ftsy_score).iloc[0]])[0]
df_data_fabu_ovr['formation'] = comparison_col[-3:]
     
# filter players
df_data_fabu_ovr = df_data_fabu_ovr.loc[df_data_fabu_ovr[comparison_col] == max_possible_ftsy_score] 
del max_possible_ftsy_score
del comparison_col
     
# refine DataFrame
df_data_fabu_ovr['topxi_lvl'] = 'OVR'
df_data_fabu_ovr['season_id'] = 0
df_data_fabu_ovr['season_name'] = None
df_data_fabu_ovr['round_name'] = -1

list_topxi_fabu_ovr_field = list_fabu_column_names
df_topxi_fabu_ovr = df_data_fabu_ovr[list_topxi_fabu_ovr_field]

log('Constructed target DataFrame df_topxi_fabu_ovr with ' + str(len(df_topxi_fabu_ovr)) + ' players')   
del df_data_fabu_ovr

# qualtiy check
df_topxi_fabu_ovr = quality_check_formations(df_topxi_fabu_ovr, ['position_short'])

###########################################
#   FANTASY BUNDESLIGA OVERALL - SEASON   #
###########################################

log_headline('(2.2/5) CALCULATING FANTASY BUNDESLIGA BY SEASON')
df_data_fabu_szn = df_data 

# per player and season: Caluclate latest team and position 
df_data_fabu_szn_tail = df_data_fabu_szn.sort_values('round_name').groupby(['season_id','player_id']).tail(1)
list_groupby_values = ['season_id','season_name','player_id','player_name','player_image_path','position_short','buli_team_id','buli_team_name','buli_team_code','buli_team_logo_path','user_id','user_name','user_team_name','user_team_code','user_team_logo_path']
df_data_fabu_szn_tail = df_data_fabu_szn_tail[list_groupby_values]

df_data_fabu_szn = df_data_fabu_szn[['player_id','season_id','season_name','kickoff_dt', 'ftsy_score']]

df_data_fabu_szn = pd.merge(
    df_data_fabu_szn
    , df_data_fabu_szn_tail
    , how = 'left'
    , left_on = ['season_id','season_name','player_id']
    , right_on = ['season_id','season_name','player_id']
    )

del df_data_fabu_szn_tail

# aggregate to 1 row per player per season
df_data_fabu_szn = aggregate_values(df_data_fabu_szn, list_groupby_values)

df_data_fabu_szn["rank"] = df_data_fabu_szn.groupby(["season_id","position_short"])["ftsy_score"].rank(method="first", ascending=False)    
df_data_fabu_szn = filter_for_candidates(df_data_fabu_szn)

# calc formation scores
list_agg_cols = ['season_id'] 
df_data_fabu_szn = calc_ftsy_scores_for_all_formations(df_data_fabu_szn, list_agg_cols)
df_data_fabu_szn = df_data_fabu_szn.sort_values(by=['season_id','ftsy_score'], ascending=False)
del list_agg_cols

# starting to calculate the actual top xi
list_topxi = []
list_season_ids = df_data_fabu_szn['season_id'].unique().tolist()

# loop over seasons and rounds
for unique_season in list_season_ids:
    
    # filter for season
    log('Calculate top xis for season_id ' + str(unique_season))
    df_tmp2 = df_data_fabu_szn.loc[df_data_fabu_szn['season_id'] == unique_season] 
     
    # define max score possible score
    df_tmp2['player_max'] = df_tmp2[['score_343','score_352','score_433','score_442','score_451','score_532','score_541']].max(axis=1)
    df_tmp2['global_max'] = df_tmp2['player_max'].max()

    # save the formation
    max_possible_ftsy_score = df_tmp2['global_max'].max()
    comparison_col = (df_tmp2.columns[(df_tmp2==max_possible_ftsy_score).iloc[0]])[0]
    df_tmp2['formation'] = comparison_col[-3:]
        
    # filter players
    df_tmp2 = df_tmp2.loc[df_tmp2[comparison_col] == max_possible_ftsy_score] 
    del max_possible_ftsy_score
    del comparison_col
        
    # select and sort columns
    df_tmp2 = df_tmp2[[e for e in list_fabu_column_names if e not in {"topxi_lvl","round_name"}]]
    df_tmp2 = df_tmp2.values.tolist()
        
    list_topxi.extend(df_tmp2)
        
# construct target DataFrame
df_topxi_fabu_szn = pd.DataFrame(list_topxi)

del list_season_ids, unique_season, list_topxi, df_tmp2, df_data_fabu_szn

# add missing columns
df_topxi_fabu_szn.insert(0, 'topxi_lvl', 'SZN')
df_topxi_fabu_szn.insert(3, 'round_name', -1)

# name all columns
df_topxi_fabu_szn.columns = list_fabu_column_names

log('Constructed target DataFrame df_topxi_fabu_szn with ' + str(len(df_topxi_fabu_ovr)) + ' players')   

# qualtiy check
df_topxi_fabu_szn = quality_check_formations(df_topxi_fabu_szn, ['season_id','position_short'])

##########################################
#   FANTASY BUNDESLIGA OVERALL - ROUND   #
##########################################

log_headline('(2.3/5) CALCULATING FANTASY BUNDESLIGA BY ROUND')
df_data_fabu_rnd = df_data 

# calc rank and filter for candidates
df_data_fabu_rnd["rank"] = df_data_fabu_rnd.groupby(["season_id","round_name","position_short"])["ftsy_score"].rank(method="first", ascending=False)    
df_data_fabu_rnd = filter_for_candidates(df_data_fabu_rnd)

# data is already on aggegation target: 1 row per player per season per round
# therefore just values have to be reassigned

df_data_fabu_rnd['ftsy_score_avg'] = df_data_fabu_rnd['ftsy_score']
df_data_fabu_rnd['appearance_cnt'] = 1
df_data_fabu_rnd['appearance_min_dt'] = df_data_fabu_rnd['kickoff_dt']
df_data_fabu_rnd['appearance_max_dt'] = df_data_fabu_rnd['kickoff_dt']

# calc formation scores: Elf der Woche
list_agg_cols = ['season_id','round_name'] 
df_data_fabu_rnd = calc_ftsy_scores_for_all_formations(df_data_fabu_rnd, list_agg_cols)
df_data_fabu_rnd = df_data_fabu_rnd.sort_values(by=['season_id','round_name','ftsy_score'], ascending=False)
del list_agg_cols

# starting to calculate the actual top xi
list_topxi = []
list_season_ids = df_data_fabu_rnd['season_id'].unique().tolist()

# loop over seasons and rounds
for unique_season in list_season_ids:
    
    # filter for seasons
    log('Calculate top xis for all rounds of season_id ' + str(unique_season))
    df_tmp = df_data_fabu_rnd.loc[df_data_fabu_rnd['season_id'] == unique_season] 
    list_round_names = df_tmp['round_name'].unique().tolist()

    for unique_round in list_round_names:
        
        # filter for rounds
        df_tmp2 = df_tmp.loc[df_tmp['round_name'] == unique_round] 
        
        # define max score possible score
        df_tmp2['player_max'] = df_tmp2[['score_343','score_352','score_433','score_442','score_451','score_532','score_541']].max(axis=1)
        df_tmp2['global_max'] = df_tmp2['player_max'].max()

        # save the formation
        max_possible_ftsy_score = df_tmp2['global_max'].max()
        comparison_col = (df_tmp2.columns[(df_tmp2==max_possible_ftsy_score).iloc[0]])[0]
        df_tmp2['formation'] = comparison_col[-3:]
        
        # filter players
        df_tmp2 = df_tmp2.loc[df_tmp2[comparison_col] == max_possible_ftsy_score] 
        del max_possible_ftsy_score
        del comparison_col
        
        # select and sort columns
        df_tmp2 = df_tmp2[[e for e in list_fabu_column_names if e not in {"topxi_lvl","aaa"}]]
        df_tmp2 = df_tmp2.values.tolist()
        
        list_topxi.extend(df_tmp2)

# construct target DataFrame
df_topxi_fabu_rnd = pd.DataFrame(list_topxi)
log('Constructed target DataFrame df_topxi_fabu_rnd with ' + str(len(df_topxi_fabu_rnd)) + ' players')   

del list_season_ids, unique_season, list_round_names, unique_round, list_topxi, df_tmp, df_tmp2, df_data_fabu_rnd

# add missing columns
df_topxi_fabu_rnd.insert(0, 'topxi_lvl', 'RND')

# name all columns
df_topxi_fabu_rnd.columns = list_fabu_column_names

# qualtiy check
df_topxi_fabu_rnd = quality_check_formations(df_topxi_fabu_rnd, ['season_id','round_name', 'position_short'])

log('Success')

#####################
#   FANTASY USERS   #
#####################

log_headline('(3/5) CALCULATING FANTASY USERS TOP XIs')

# define target structure of database table
list_user_column_names = [
    'topxi_lvl'
    ,'season_id'
    ,'season_name'
    ,'formation'
    ,'position_short'
    ,'player_id'
    ,'player_name'
    ,'player_image_path'
    ,'buli_team_id'
    ,'buli_team_code'
    ,'buli_team_name'
    ,'buli_team_logo_path'
    ,'user_id'
    ,'user_name'
    ,'user_team_name'
    ,'user_team_code'
    ,'user_team_logo_path'
    ,'ftsy_score'
    ,'ftsy_score_avg'
    ,'appearance_cnt'
    ,'appearance_min_dt'
    ,'appearance_max_dt'
    ]

################################
#   FANTASY TEAMS - ALL TIME   #
################################

log_headline('(3.1/5) CALCULATING FANTASY TEAM BY USER ALL TIME')
df_data_user_ovr = df_data 

# filter for players that were on users roster
df_data_user_ovr = df_data_user_ovr.loc[((df_data['user_id'] > 0))] 

# per player and season: Caluclate latest team and position 
df_data_user_ovr_tail = df_data_user_ovr.sort_values(['season_id','round_name']).groupby(['user_id','player_id']).tail(1)
list_groupby_values = ['user_id','user_name','user_team_name','user_team_code','user_team_logo_path','player_id','player_name','player_image_path','position_short','buli_team_id','buli_team_name','buli_team_code','buli_team_logo_path']
df_data_user_ovr_tail = df_data_user_ovr_tail[list_groupby_values]

df_data_user_ovr = df_data_user_ovr[['player_id','user_id','kickoff_dt','ftsy_score']]

df_data_user_ovr = pd.merge(
    df_data_user_ovr
    , df_data_user_ovr_tail
    , how = 'left'
    , left_on = ['user_id','player_id']
    , right_on = ['user_id','player_id']
    )

del df_data_user_ovr_tail

# aggregate to 1 row per player per season
df_data_user_ovr = aggregate_values(df_data_user_ovr, list_groupby_values)

df_data_user_ovr["rank"] = df_data_user_ovr.groupby(["user_id","position_short"])["ftsy_score"].rank(method="first", ascending=False)    
df_data_user_ovr = filter_for_candidates(df_data_user_ovr)

# calc formation scores
list_agg_cols = ['user_id'] 
df_data_user_ovr = calc_ftsy_scores_for_all_formations(df_data_user_ovr, list_agg_cols)
df_data_user_ovr = df_data_user_ovr.sort_values(by=['user_id','ftsy_score'], ascending=False)
del list_agg_cols

# starting to calculate the actual top xi
list_topxi = []
list_user_ids = df_data_user_ovr['user_id'].unique().tolist()

# loop over seasons and rounds
for unique_user in list_user_ids:
    
    # filter for season
    log('Calculate top xis for user ' + str(unique_user))
    df_tmp2 = df_data_user_ovr.loc[df_data_user_ovr['user_id'] == unique_user] 
     
    # define max score possible score
    df_tmp2['player_max'] = df_tmp2[['score_343','score_352','score_433','score_442','score_451','score_532','score_541']].max(axis=1)
    df_tmp2['global_max'] = df_tmp2['player_max'].max()

    # save the formation
    max_possible_ftsy_score = df_tmp2['global_max'].max()
    comparison_col = (df_tmp2.columns[(df_tmp2==max_possible_ftsy_score).iloc[0]])[0]
    df_tmp2['formation'] = comparison_col[-3:]
        
    # filter players
    df_tmp2 = df_tmp2.loc[df_tmp2[comparison_col] == max_possible_ftsy_score] 
    del max_possible_ftsy_score
    del comparison_col
    
    # select and sort columns
    df_tmp2 = df_tmp2[[e for e in list_user_column_names if e not in {"topxi_lvl","season_id","season_name"}]]
    df_tmp2 = df_tmp2.values.tolist()
        
    list_topxi.extend(df_tmp2)
        
# construct target DataFrame
df_topxi_user_ovr = pd.DataFrame(list_topxi)

# add missing columns
df_topxi_user_ovr.insert(0, 'topxi_lvl', 'OVR')
df_topxi_user_ovr.insert(1, 'season_id', '0')
df_topxi_user_ovr.insert(2, 'season_name', None)

del list_user_ids, unique_user, list_topxi, df_tmp2, df_data_user_ovr

# name all columns
df_topxi_user_ovr.columns = list_user_column_names

log('Constructed target DataFrame df_topxi_fabu_szn with ' + str(len(df_topxi_user_ovr)) + ' players')   

# qualtiy check
df_topxi_user_ovr = quality_check_formations(df_topxi_user_ovr, ['user_id','position_short'])

log('Success')

##############################
#   FANTASY TEAMS - SEASON   #
##############################

log_headline('(3.2/5) CALCULATING FANTASY TEAM BY USER AND SEASON')
df_data_user_szn = df_data 

# filter for players that were on users roster
df_data_user_szn = df_data_user_szn.loc[((df_data['user_id'] > 0))] 

# per user and season: Caluclate latest team and position 
df_data_user_szn_tail = df_data_user_szn.sort_values(['season_id','round_name']).groupby(['season_id','user_id','player_id']).tail(1)
list_groupby_values = ['season_id','season_name','user_id','user_name','user_team_name','user_team_code','user_team_logo_path','player_id','player_name','player_image_path','position_short','buli_team_id','buli_team_name','buli_team_code','buli_team_logo_path']
df_data_user_szn_tail = df_data_user_szn_tail[list_groupby_values]

df_data_user_szn = df_data_user_szn[['player_id','user_id','season_id', 'kickoff_dt', 'ftsy_score']]

df_data_user_szn = pd.merge(
    df_data_user_szn
    , df_data_user_szn_tail
    , how = 'left'
    , left_on = ['user_id','player_id','season_id']
    , right_on = ['user_id','player_id','season_id']
    )

del df_data_user_szn_tail

# aggregate to 1 row per player per season
df_data_user_szn = aggregate_values(df_data_user_szn, list_groupby_values)

df_data_user_szn["rank"] = df_data_user_szn.groupby(["user_id","season_id","position_short"])["ftsy_score"].rank(method="first", ascending=False)    
df_data_user_szn = filter_for_candidates(df_data_user_szn)

# calc formation scores
list_agg_cols = ['user_id','season_id'] 
df_data_user_szn = calc_ftsy_scores_for_all_formations(df_data_user_szn, list_agg_cols)
df_data_user_szn = df_data_user_szn.sort_values(by=['user_id','season_id','ftsy_score'], ascending=False)
del list_agg_cols

# starting to calculate the actual top xi

list_topxi = []
list_season_ids = df_data_user_szn['season_id'].unique().tolist()

# loop over seasons and rounds
for unique_season in list_season_ids:
    
    # filter for season
    log('Calculate top xis for all users of season_id ' + str(unique_season))
    df_tmp = df_data_user_szn.loc[df_data_user_szn['season_id'] == unique_season] 
    list_user_ids = df_tmp['user_id'].unique().tolist()

    for unique_user in list_user_ids:
        
        # filter for round
        df_tmp2 = df_tmp.loc[df_tmp['user_id'] == unique_user] 
        
        # define max score possible score
        df_tmp2['player_max'] = df_tmp2[['score_343','score_352','score_433','score_442','score_451','score_532','score_541']].max(axis=1)
        df_tmp2['global_max'] = df_tmp2['player_max'].max()

        # safe the formation
        max_possible_ftsy_score = df_tmp2['global_max'].max()
        comparison_col = (df_tmp2.columns[(df_tmp2==max_possible_ftsy_score).iloc[0]])[0]
        df_tmp2['formation'] = comparison_col[-3:]
        
        # filter players
        df_tmp2 = df_tmp2.loc[df_tmp2[comparison_col] == max_possible_ftsy_score] 
        del max_possible_ftsy_score
        del comparison_col
        
        # select and sort columns
        df_tmp2 = df_tmp2[[e for e in list_user_column_names if e not in {"topxi_lvl"}]]
        df_tmp2 = df_tmp2.values.tolist()
                
        list_topxi.extend(df_tmp2)
        
# construct target DataFrame
df_topxi_user_szn = pd.DataFrame(list_topxi)
del list_season_ids, unique_season, list_user_ids, unique_user, list_topxi, df_tmp, df_tmp2, df_data_user_szn

# add missing columns
df_topxi_user_szn.insert(0, 'topxi_lvl', 'SZN')

# name all columns
df_topxi_user_szn.columns = list_user_column_names

log('Constructed target DataFrame df_topxi_user_szn with ' + str(len(df_topxi_user_szn)) + ' players')   

# qualtiy check
df_topxi_user_szn = quality_check_formations(df_topxi_user_szn, ['user_id','season_id','position_short'])

# sort
df_topxi_user_szn = df_topxi_user_szn.sort_values(by=['user_id','season_id','position_short','ftsy_score'], ascending=False)

log('Success')

########################
#   BUNDESLIGA TEAMS   #
########################

log_headline('(4/5) CALCULATING FANTASY BULI TEAMS TOP XIs')

# define target structure of database table
list_buli_column_names = [
    'topxi_lvl'
    ,'season_id'
    ,'season_name'
    ,'formation'
    ,'position_short'
    ,'player_id'
    ,'player_name'
    ,'player_image_path'
    ,'buli_team_id'
    ,'buli_team_code'
    ,'buli_team_name'
    ,'buli_team_logo_path'
    ,'user_id'
    ,'user_name'
    ,'user_team_name'
    ,'user_team_code'
    ,'user_team_logo_path'
    ,'ftsy_score'
    ,'ftsy_score_avg'
    ,'appearance_cnt'
    ,'appearance_min_dt'
    ,'appearance_max_dt'
    ]

###################################
#   BUNDESLIGA TEAMS - ALL TIME   #
###################################

log_headline('(4.1/5) CALCULATING FANTASY TEAM BY BUNDESLIGA TEAM ALL TIME')
df_data_buli_ovr = df_data 
df_data_buli_ovr = df_data_buli_ovr.loc[df_data_buli_ovr['buli_team_id'].notna()] 

# per player and season: Caluclate latest team and position 
df_data_buli_ovr_tail = df_data_buli_ovr.sort_values(['season_id','round_name']).groupby(['player_id','buli_team_id']).tail(1)
list_groupby_values = ['user_id','user_name','user_team_name','user_team_code','user_team_logo_path','player_id','player_name','player_image_path','position_short','buli_team_id','buli_team_name','buli_team_code','buli_team_logo_path']
df_data_buli_ovr_tail = df_data_buli_ovr_tail[list_groupby_values]

df_data_buli_ovr = df_data_buli_ovr[['player_id','buli_team_id','kickoff_dt','ftsy_score']]

df_data_buli_ovr = pd.merge(
    df_data_buli_ovr
    , df_data_buli_ovr_tail
    , how = 'left'
    , left_on = ['player_id','buli_team_id']
    , right_on = ['player_id','buli_team_id']
    )

del df_data_buli_ovr_tail

# aggregate to 1 row per player per team
df_data_buli_ovr = aggregate_values(df_data_buli_ovr, list_groupby_values)

df_data_buli_ovr["rank"] = df_data_buli_ovr.groupby(["buli_team_id","position_short"])["ftsy_score"].rank(method="first", ascending=False)    
df_data_buli_ovr = filter_for_candidates(df_data_buli_ovr)

# calc formation scores
list_agg_cols = ['buli_team_id'] 
df_data_buli_ovr = calc_ftsy_scores_for_all_formations(df_data_buli_ovr, list_agg_cols)
df_data_buli_ovr = df_data_buli_ovr.sort_values(by=['buli_team_id','ftsy_score'], ascending=False)
del list_agg_cols

# starting to calculate the actual top xi
list_topxi = []
list_team_ids = df_data_buli_ovr['buli_team_id'].unique().tolist()

# loop over teams
for unique_team in list_team_ids:
    
    # filter for team
    log('Calculate top xis for buli team ' + str(unique_team))
    df_tmp2 = df_data_buli_ovr.loc[df_data_buli_ovr['buli_team_id'] == unique_team] 
     
    # define max score possible score
    df_tmp2['player_max'] = df_tmp2[['score_343','score_352','score_433','score_442','score_451','score_532','score_541']].max(axis=1)
    df_tmp2['global_max'] = df_tmp2['player_max'].max()

    # save the formation
    max_possible_ftsy_score = df_tmp2['global_max'].max()
    comparison_col = (df_tmp2.columns[(df_tmp2==max_possible_ftsy_score).iloc[0]])[0]
    df_tmp2['formation'] = comparison_col[-3:]
        
    # filter players
    df_tmp2 = df_tmp2.loc[df_tmp2[comparison_col] == max_possible_ftsy_score] 
    del max_possible_ftsy_score
    del comparison_col
    
    # select and sort columns
    df_tmp2 = df_tmp2[[e for e in list_buli_column_names if e not in {"topxi_lvl","season_id","season_name"}]]
    df_tmp2 = df_tmp2.values.tolist()
        
    list_topxi.extend(df_tmp2)
        
# construct target DataFrame
df_topxi_buli_ovr = pd.DataFrame(list_topxi)

# add missing columns
df_topxi_buli_ovr.insert(0, 'topxi_lvl', 'OVR')
df_topxi_buli_ovr.insert(1, 'season_id', '0')
df_topxi_buli_ovr.insert(2, 'season_name', None)

del list_team_ids, unique_team, list_topxi, df_tmp2, df_data_buli_ovr

# name all columns
df_topxi_buli_ovr.columns = list_buli_column_names

log('Constructed target DataFrame df_topxi_buli_ovr with ' + str(len(df_topxi_buli_ovr)) + ' players')   

# qualtiy check
df_topxi_buli_ovr = quality_check_formations(df_topxi_buli_ovr, ['buli_team_id','position_short'])

log('Success')

#################################
#   BUNDESLIGA TEAMS - SEASON   #
#################################

log_headline('(4.2/5) CALCULATING FANTASY TEAM BY BUNDESLIGA TEAM BY SEASON')
df_data_buli_szn = df_data 
df_data_buli_szn = df_data_buli_szn.loc[df_data_buli_szn['buli_team_id'].notna()] 

# per team and season: Caluclate latest team and position 
df_data_buli_szn_tail = df_data_buli_szn.sort_values(['season_id','round_name']).groupby(['season_id','buli_team_id','player_id']).tail(1)
list_groupby_values = ['season_id','season_name','user_id','user_name','user_team_name','user_team_code','user_team_logo_path','player_id','player_name','player_image_path','position_short','buli_team_id','buli_team_name','buli_team_code','buli_team_logo_path']
df_data_buli_szn_tail = df_data_buli_szn_tail[list_groupby_values]

df_data_buli_szn = df_data_buli_szn[['player_id','buli_team_id','season_id', 'kickoff_dt', 'ftsy_score']]

df_data_buli_szn = pd.merge(
    df_data_buli_szn
    , df_data_buli_szn_tail
    , how = 'left'
    , left_on = ['buli_team_id','player_id','season_id']
    , right_on = ['buli_team_id','player_id','season_id']
    )

del df_data_buli_szn_tail

# aggregate to 1 row per player per season
df_data_buli_szn = aggregate_values(df_data_buli_szn, list_groupby_values)

df_data_buli_szn["ftsy_score_avg"] = df_data_buli_szn["ftsy_score_avg"].round(1) 
df_data_buli_szn["rank"] = df_data_buli_szn.groupby(["buli_team_id","season_id","position_short"])["ftsy_score"].rank(method="first", ascending=False)    
df_data_buli_szn = filter_for_candidates(df_data_buli_szn)

# calc formation scores
list_agg_cols = ['buli_team_id','season_id'] 
df_data_buli_szn = calc_ftsy_scores_for_all_formations(df_data_buli_szn, list_agg_cols)
df_data_buli_szn = df_data_buli_szn.sort_values(by=['buli_team_id','season_id','ftsy_score'], ascending=False)
del list_agg_cols

# starting to calculate the actual top xi

list_topxi = []
list_season_ids = df_data_buli_szn['season_id'].unique().tolist()

# loop over seasons and rounds
for unique_season in list_season_ids:
    
    # filter for season
    log('Calculate top xis for all teams of season_id ' + str(unique_season))
    df_tmp = df_data_buli_szn.loc[df_data_buli_szn['season_id'] == unique_season] 
    list_team_ids = df_tmp['buli_team_id'].unique().tolist()

    for unique_team in list_team_ids:
        
        # filter for round
        df_tmp2 = df_tmp.loc[df_tmp['buli_team_id'] == unique_team] 
        
        # define max score possible score
        df_tmp2['player_max'] = df_tmp2[['score_343','score_352','score_433','score_442','score_451','score_532','score_541']].max(axis=1)
        df_tmp2['global_max'] = df_tmp2['player_max'].max()

        # safe the formation
        max_possible_ftsy_score = df_tmp2['global_max'].max()
        comparison_col = (df_tmp2.columns[(df_tmp2==max_possible_ftsy_score).iloc[0]])[0]
        df_tmp2['formation'] = comparison_col[-3:]
        
        # filter players
        df_tmp2 = df_tmp2.loc[df_tmp2[comparison_col] == max_possible_ftsy_score] 
        del max_possible_ftsy_score
        del comparison_col
        
        # select and sort columns
        df_tmp2 = df_tmp2[[e for e in list_user_column_names if e not in {"topxi_lvl"}]]
        df_tmp2 = df_tmp2.values.tolist()
                
        list_topxi.extend(df_tmp2)
        
# construct target DataFrame
df_topxi_buli_szn = pd.DataFrame(list_topxi)
del list_season_ids, unique_season, list_team_ids, unique_team, list_topxi, df_tmp, df_tmp2, df_data_buli_szn

# add missing columns
df_topxi_buli_szn.insert(0, 'topxi_lvl', 'SZN')

# name all columns
df_topxi_buli_szn.columns = list_user_column_names

log('Constructed target DataFrame df_topxi_buli_szn with ' + str(len(df_topxi_buli_szn)) + ' players')   

# qualtiy check
df_topxi_buli_szn = quality_check_formations(df_topxi_buli_szn, ['buli_team_id','season_id','position_short'])

# sort
df_topxi_buli_szn = df_topxi_buli_szn.sort_values(by=['buli_team_id','season_id','position_short','ftsy_score'], ascending=False)

log('Success')

#################################
#   WRITE RESULTS TO MYSQL DB   #
#################################

del df_data
log_headline('5/5) WRITE RESULTS TO MYSQL DB')

# Fantasy-Bundesliga
df_topxi_fabu_ovr = pd.concat([df_topxi_fabu_ovr, df_topxi_fabu_szn, df_topxi_fabu_rnd], ignore_index=True)
df_topxi_fabu_ovr = df_topxi_fabu_ovr.sort_values(by=['topxi_lvl','season_id','round_name','formation','position_short','ftsy_score'], ascending=True)
write_to_database(df_topxi_fabu_ovr, engine, 'topxi_fabu_ovr', '../sql/db/topxi_fabu_ovr.sql')    

# Fantasy-Teams
df_topxi_user_ovr = pd.concat([df_topxi_user_ovr, df_topxi_user_szn], ignore_index=True)
df_topxi_user_ovr = df_topxi_user_ovr.sort_values(by=['topxi_lvl','season_id','user_id','formation','position_short','ftsy_score'], ascending=True)
write_to_database(df_topxi_user_ovr, engine, 'topxi_ftsy_team', '../sql/db/topxi_ftsy_team.sql')    

# Bundesliga-Teams
df_topxi_buli_ovr = pd.concat([df_topxi_buli_ovr, df_topxi_buli_szn], ignore_index=True)
df_topxi_buli_ovr = df_topxi_buli_ovr.sort_values(by=['topxi_lvl','season_id','buli_team_id','formation','position_short','ftsy_score'], ascending=True)
write_to_database(df_topxi_buli_ovr, engine, 'topxi_buli_team', '../sql/db/topxi_buli_team.sql')    

log('Success')