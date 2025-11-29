#!/usr/bin/env python3.6.3
# -*- coding: utf-8 -*-
"""
Created on 2025-11-16 22:00:00

Predicts the ftsy_score per minute of all players.

@author: lennart

"""

import sys 
sys.path.insert(1, '../secrets/')
sys.path.insert(2, '../py/')
from logging_function import log, log_headline

log("Importing libraries")

import gc
import pandas as pd
import numpy as np
from sqlalchemy import create_engine
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score

def safe_float32(df):
    for col in df.columns:
        df[col] = pd.to_numeric(df[col], errors='coerce')
        df[col] = df[col].astype("float32")
    return df

# ------------------------------------------------------------
# 1) Read meta data
# ------------------------------------------------------------

log('Get meta data from database')

# Get current season and round
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

with engine.connect() as con:
    log("Querying meta-data from table parameter")
    sql_select = con.execute('SELECT season_id, spieltag FROM parameter LIMIT 1')
    sql_first_row = sql_select.fetchone()
    current_season_id =  sql_first_row['season_id']
    current_round_name =  sql_first_row['spieltag']

log("Current season_id: " + str(current_season_id))
log("Current round_name: " + str(current_round_name))

with engine.connect() as con:

    # Drop old table
    log("Dropping feature table")
    con.execute('DROP TABLE IF EXISTS ml_player_features_score;')
    
    # Create new table
    log("Recreating feature table")
    with open("../sql/snippets/ml-create-feature-table-score.sql", "r", encoding="utf-8") as f:
        create_ml_player_features_score = f.read()

    con.execute(create_ml_player_features_score)

# Define test size sample in number of rounds
test_size_parameter = 3

positions = ["AW", "MF", "ST", "TW"]

for pos in positions:

    log_headline(f"Loading data for position: {pos}")

    # Adjust test / train size by position 
    if pos == 'TW':
        test_size = 10
    elif pos == 'AW':
        test_size = 4
    elif pos == 'MF':
        test_size = 6    
    elif pos == 'ST':
        test_size = 10
    else:
        test_size = test_size_parameter

    # Define test / train / rounds
    query_test_rounds = f'''
        SELECT DISTINCT
            CONCAT(r.season_id, "-", r.name) AS season_round
        FROM sm_rounds r
        WHERE
            1 = CASE 
                    WHEN {current_season_id} = r.season_id AND r.name < {current_round_name} THEN 1
                    WHEN {current_season_id} > r.season_id THEN 1 
                    ELSE 0 END
        ORDER BY r.season_id DESC, r.name DESC
        LIMIT {test_size}
        '''

    with engine.connect() as con:
        df_test_rounds = pd.read_sql(query_test_rounds, con)

    # Getting training data
    log("Loading training data")

    query_train = f'''
        SELECT 
            *
            , actual_ftsy_score AS target_ftsy_score
        FROM ml_player_projection_features
        WHERE 
            position_short = '{pos}'
            AND COALESCE(actual_minutes_played_stat, 0) >= 80
            AND target_flg = 0
            AND CONCAT(season_id, '-', round_name) NOT IN ({",".join([f"'{sr}'" for sr in df_test_rounds['season_round']])})
        '''

    df_train_iter = pd.read_sql(query_train, engine, chunksize=500)
    df_train = pd.concat(df_train_iter, ignore_index=True)
  
    log(f"Rows for df_train: {len(df_train)}")

    # Getting test + live data
    query_test = f'''
        SELECT 
            *
            , actual_ftsy_score AS target_ftsy_score
        FROM ml_player_projection_features
        WHERE 
            position_short = '{pos}'
            AND COALESCE(actual_minutes_played_stat, 0) >= 80
            AND target_flg = 0
            AND CONCAT(season_id, '-', round_name) IN ({",".join([f"'{sr}'" for sr in df_test_rounds['season_round']])})
        '''

    query_live = f'''
        SELECT 
            *
            , NULL AS target_ftsy_score
        FROM ml_player_projection_features
        WHERE 
            position_short = '{pos}'
            AND season_id = {current_season_id} 
            AND round_name = {current_round_name}
        '''

    log("Loading test + live data")
    with engine.connect() as con:
        df_test = pd.read_sql(query_test, con)
        df_live = pd.read_sql(query_live, con)

    log(f"Rows for df_test: {len(df_test)}")
    log(f"Rows for df_live: {len(df_live)}")

    # ------------------------------------------------------------
    # 2) Define Features / Target
    # ------------------------------------------------------------

    TARGET = "target_ftsy_score"
    FEATURES = [
        col for col in df_train.columns
        if "_feat_" in col and col != TARGET
    ]

    # Remove some features again

    remove_features = [
        "player_feat_stat_appearance_avg_5"
        , "player_feat_ftsy_score_avg_5"
    ]
    
    if pos != 'TW':
        remove_features += [
            "player_feat_stat_goalsagainstgk_avg_5"
            , "player_feat_stat_gksaves_avg_5"
            , "player_feat_stat_goalsagainstgk_teamshare_avg"
        ]

    if pos != 'TW' and pos != 'AW':
        remove_features += [
            "player_feat_stat_cleansheet_avg_5",
            "player_feat_stat_goalsagainst_avg_5"
        ]

    if pos == "TW":   
        remove_features += [
            "player_feat_stat_intblocktackle_avg_5",
            "player_feat_stat_keypasses_avg_5",
            "player_feat_stat_dribbles_avg_5",
            "player_feat_stat_scoring_avg_5",
            "player_feat_stat_shots_avg_5",
            "player_feat_stat_crosses_avg_5",
            "player_feat_stat_passing_avg_5",
            "player_feat_stat_goalsagainst_avg_5",
            "player_feat_stat_clearances_avg_5",
            "player_feat_stat_duels_avg_5",
            "player_feat_stat_passes_teamshare_avg",
            "player_feat_stat_shot_teamshare_avg",
            "player_feat_stat_duels_teamshare_avg"           
        ]

    FEATURES = [f for f in FEATURES if f not in remove_features]

    log(f"Target für das Modell: {TARGET}")
    log(f"Anzahl Features: {len(FEATURES)}")

    # ------------------------------------------------------------
    # 3) Train/Test Split
    # ------------------------------------------------------------

    log_headline(f"Training for position: {pos}")

    log(f"Number of records Train: {len(df_train)} | Test: {len(df_test)} | Live: {len(df_live)}")

    # ----------------------------------------------------
    # 4) Features / Targets
    # ----------------------------------------------------
    
    X_train = df_train[FEATURES]
    y_train = df_train[TARGET]

    X_test = df_test[FEATURES]
    y_test = df_test[TARGET]

    X_live = df_live[FEATURES]

    X_train = X_train.fillna(0)
    X_test  = X_test.fillna(0)
    X_live  = X_live.fillna(0)

    X_train = safe_float32(X_train)
    X_test = safe_float32(X_test)
    X_live = safe_float32(X_live)

    y_train = y_train.replace([np.inf, -np.inf], np.nan).fillna(0).astype("float32")
    y_test  = y_test.replace([np.inf, -np.inf], np.nan).fillna(0).astype("float32")

    # Clip the Y-Value (TARGET) to reduce variance on high scoring
    y_train = y_train.clip(upper=35)
    y_test  = y_test.clip(upper=35)

    bad_cols = X_train.columns[X_train.isna().any()]
    if len(bad_cols) > 0:
        print("NAN in columns:", bad_cols.tolist())

    inf_cols = X_train.columns[np.isinf(X_train).any()]
    if len(inf_cols) > 0:
        print("INF in columns:", inf_cols.tolist())

    # ----------------------------------------------------
    # 5) Training
    # ----------------------------------------------------

    model = RandomForestRegressor(
        n_estimators=200,
        max_depth=12,
        min_samples_leaf=5,
        random_state=42,
        n_jobs=1
    )

    model.fit(X_train, y_train)

    # ----------------------------------------------------
    # 6) Evaluation (Test)
    # ----------------------------------------------------
    
    if len(df_test) > 0:
        preds = model.predict(X_test)
        mae = mean_absolute_error(y_test, preds)
        r2 = r2_score(y_test, preds)
        rmse = np.sqrt(mean_squared_error(y_test, preds))
        print("R²:", round(r2, 4))
        print("MAE:", round(mae, 4))
        print("RMSE:", round(rmse, 4))
    else:
        print("No Test-Set available")

    # Feature importance
    importances = model.feature_importances_
    feature_names = X_train.columns

    fi = sorted(zip(feature_names, importances), key=lambda x: x[1], reverse=True)

    print("\nFeature Importances:")
    for name, imp in fi:
        print(f"{name:35s} {imp:.4f}")

    # cleanup to reduce RAM
    del fi, feature_names, importances, preds, mae, r2, rmse, bad_cols, inf_cols

    # ----------------------------------------------------
    # 7) Live Predictions
    # ----------------------------------------------------

    if len(df_live) > 0:
        live_preds = model.predict(X_live)
        
        del df_train_iter, df_train, df_test

        df_live_output = pd.DataFrame({
            'season_id': df_live['season_id'].astype(int),
            'round_name': df_live['round_name'].astype(int),
            'player_id': df_live['player_id'].astype(int),
            'position_short': df_live['position_short'].astype(str),
            'predicted_ftsy_score': live_preds.astype(float),
        })

        log('Writing to database')
        try:
            create_table_query = f"""
                CREATE TABLE IF NOT EXISTS ml_player_projection (
                    season_id INT,
                    round_name INT,
                    player_id INT,
                    position_short VARCHAR(10),
                    predicted_ftsy_score FLOAT
                )
            """
        
            with engine.connect() as con:
                con.execute(create_table_query)
            
            delete_query = f"""
                DELETE FROM ml_player_projection
                WHERE season_id = {current_season_id}
                  AND round_name = {current_round_name}
                  AND position_short = '{pos}'
            """
            with engine.connect() as con:
                con.execute(delete_query)

            df_live_output.to_sql(
                'ml_player_projection',
                con=engine,
                if_exists='append',
                index=False
            )

        except Exception as e:
            log(f"Fehler beim Speichern der LIVE Predictions für {pos}: {e}")

    # cleanup to reduce RAM
    del df_live, X_train, X_test, X_live, y_train, y_test, df_test_rounds
    gc.collect()