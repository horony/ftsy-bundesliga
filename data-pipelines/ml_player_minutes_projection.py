#!/usr/bin/env python3.6.3
# -*- coding: utf-8 -*-
"""
Created on 2025-11-22 18:13:22

Predicts the played minutes of all players using classification models.

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
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score

def safe_float32(df):
    for col in df.columns:
        df[col] = pd.to_numeric(df[col], errors='coerce')
        df[col] = df[col].astype("float32")
    return df

def minute_to_class(m):
    if m <= 5:
        return 0 # Bench
    elif m <= 15:
        return 1 # Late Sub in
    elif m <= 45:
        return 2 # Half Sub
    elif m <= 65:
        return 3 # Late Sub out    
    else:
        return 4 # Starter

# ------------------------------------------------------------
# 1) Read data
# ------------------------------------------------------------

log('Connecting to database')

# connect to  database
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
    con.execute('DROP TABLE IF EXISTS ml_player_features_minutes;')
    
    # Create new table
    log("Recreating feature table")
    with open("../sql/snippets/ml-create-feature-table-minutes.sql", "r", encoding="utf-8") as f:
        create_ml_player_minutes_features = f.read()

    con.execute(create_ml_player_minutes_features)

# Define test size sample in number of rounds
test_size_parameter = 3

positions = ["AW", "MF", "ST", "TW"]
batch_size = 500

rows = [] 

log("Starting to iterate positions")
for pos in positions:

    log_headline(f"Loading data for position: {pos}")

    # Adjust test / train size by position 
    if pos == 'TW':
        test_size = test_size_parameter * 3
    elif pos == 'ST':
        test_size = test_size_parameter * 2
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

    offset = 0
    batch_num = 1

    log("Loading training data")
    while True:
        log(f"Loading {pos} – Batch {batch_num} (OFFSET {offset})")
        query_train = f'''
            SELECT feat.*
            FROM ml_player_minutes_features feat
            INNER JOIN (
                SELECT season_id
                FROM sm_seasons
                ORDER BY season_id DESC
                LIMIT 2
                ) sea
                ON feat.season_id = sea.season_id
            WHERE 
                feat.position_short = '{pos}'
                AND (feat.season_id != {current_season_id} AND feat.round_name != {current_round_name})
                AND CONCAT(feat.season_id, '-', feat.round_name) NOT IN ({",".join([f"'{sr}'" for sr in df_test_rounds['season_round']])})
            LIMIT {batch_size} OFFSET {offset}
            '''

        with engine.connect() as con:
            chunk = pd.read_sql(query_train, con)
        
        if chunk.empty:
            break

        rows.extend(chunk.to_dict(orient="records"))

        offset += batch_size
        batch_num += 1

    df_train = pd.DataFrame(rows)
    log(f"Rows for df_train: {len(df_train)}")

    query_test = f'''
        SELECT *
        FROM ml_player_minutes_features
        WHERE 
            position_short = '{pos}'
            AND CONCAT(season_id, '-', round_name) IN ({",".join([f"'{sr}'" for sr in df_test_rounds['season_round']])})
        '''

    query_live = f'''
        SELECT *
        FROM ml_player_minutes_features
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
    # 2) Define Features and Targets
    # ------------------------------------------------------------

    df_train["target_class"] = df_train["minutes_target"].apply(minute_to_class)
    df_test["target_class"]  = df_test["minutes_target"].apply(minute_to_class)

    TARGET = "minutes_target"
    FEATURES = [
        "appearance_lag1"
        , "minutes_lag1"
        , "minutes_lag2"
        , "minutes_lag3"
        , "minutes_lag4"
        , "minutes_lag5"
        , "minutes_roll5_avg"
        , "minutes_roll5_std"
        , "played_last5_cnt"
        , "dnp_streak"
        , "is_card_suspended"
        , "team_players_last_round"
        , "team_minutes_std_last"
        , "team_minutes_variance_last3"
        , "team_avg_minutes_last3"
        , "team_player_change_from_last"
        ]

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

    # Data cleaning
    y_train = y_train.replace([np.inf, -np.inf], np.nan).fillna(0).astype("float32")
    y_test  = y_test.replace([np.inf, -np.inf], np.nan).fillna(0).astype("float32")

    bad_cols = X_train.columns[X_train.isna().any()]
    if len(bad_cols) > 0:
        print("NAN in columns:", bad_cols.tolist())

    inf_cols = X_train.columns[np.isinf(X_train).any()]
    if len(inf_cols) > 0:
        print("INF in columns:", inf_cols.tolist())
    
    # ----------------------------------------------------
    # 5) Training
    # ----------------------------------------------------

    model = RandomForestClassifier(
        n_estimators=150,
        max_depth=8,
        min_samples_leaf=4,
        class_weight="balanced",
        random_state=42,
        n_jobs=1
    )

    model.fit(X_train, df_train["target_class"])  

    # ----------------------------------------------------
    # 6) Evaluation
    # ----------------------------------------------------

    # Feature Importance
    importances = model.feature_importances_
    feature_names = X_train.columns

    fi = sorted(zip(feature_names, importances), key=lambda x: x[1], reverse=True)

    print("\nFeature Importances (Classifier):")
    for name, imp in fi:
        print(f"{name:35s} {imp:.4f}")  
    
    # Accuracy
    pred_class = model.predict(X_test)
    accuracy = (pred_class == df_test["target_class"]).mean()
    print("Class Accuracy:", accuracy)

    rows = []
    del chunk, fi, feature_names, importances, bad_cols, inf_cols

    # ----------------------------------------------------
    # 7) Live Predictions
    # ----------------------------------------------------

    if len(df_live) > 0:
        live_class = model.predict(X_live)
        
        del df_train, df_test

        CLASS_TO_MINUTES = {
            0: 0,   # Bench
            1: 10,  # Late Sub
            2: 35,  # Half Sub
            3: 65,  # Late Sub Out
            4: 90   # Starter
        }

        # ----------------------------------------------------
        # 8) Write to database
        # ----------------------------------------------------

        df_live_output = pd.DataFrame({
            'season_id': df_live['season_id'].astype(int),
            'round_name': df_live['round_name'].astype(int),
            'player_id': df_live['player_id'].astype(int),
            'position_short': df_live['position_short'].astype(str),
            'predicted_minutes': live_class.astype(float),
        })

        df_live_output["predicted_minutes"] = [CLASS_TO_MINUTES[c] for c in live_class]


        try:
            create_table_query = f"""
                CREATE TABLE IF NOT EXISTS ml_player_minutes_projection (
                    season_id INT,
                    round_name INT,
                    player_id INT,
                    position_short VARCHAR(10),
                    predicted_minutes FLOAT
                )
            """
        
            with engine.connect() as con:
                con.execute(create_table_query)
            
            # Cleanup before insert
            delete_query = f"""
                DELETE FROM ml_player_minutes_projection
                WHERE season_id = {current_season_id}
                  AND round_name = {current_round_name}
                  AND position_short = '{pos}'
            """
            with engine.connect() as con:
                con.execute(delete_query)

            # Insert
            df_live_output.to_sql(
                'ml_player_minutes_projection',
                con=engine,
                if_exists='append',
                index=False
            )

        except Exception as e:
            log(f"Fehler beim Speichern der LIVE Predictions für {pos}: {e}")

    del df_live, X_train, X_test, X_live, y_train, y_test, df_test_rounds, df_live_output
    gc.collect()