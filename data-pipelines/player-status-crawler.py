#!/usr/bin/env python3.6.3
# -*- coding: utf-8 -*-
"""
Created on 2025-12-23 18:13:22

Crawling player injury + suspension data and (fuzzy) matching them to existing players in database.

@author: lennart

"""

import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin, urlparse
import os
import pandas as pd
from sqlalchemy import create_engine, text
import sys
sys.path.insert(1, '../secrets/')
sys.path.insert(2, '../py/')
from logging_function import log, log_headline
import re
import unicodedata

################################
#   FUNCTIONS FOR THIS SCRIPT  #
################################

def remove_special_chars(col: pd.Series) -> pd.Series:
    """
    Removing special characters from pandas strings to improve player name matching.
    """
    return col.apply(
        lambda x: (
            unicodedata.normalize("NFKD", x)
            .encode("ascii", "ignore")
            .decode("ascii")
            if isinstance(x, str)
            else x
        )
    )

def clean_name(name):
    """
    Removing abbrevations and other special characters from pandas strings to improve player name matching.
    """

    # Remove initials (single letters followed by a dot)
    name = re.sub(r'\b[A-Z]\.\s*', '', name)
    # Replace hyphens with spaces
    name = name.replace('-', ' ')
    # Remove extra spaces
    name = re.sub(r'\s+', ' ', name).strip()
    return name

def name_match(cleaned_name, full_name):
    """
    Fuzzy matching: Function to check if all parts of cleaned name are substrings of full name
    """
    parts = cleaned_name.split()  # split into parts
    return all(part in full_name for part in parts)

def scrape_bundesliga_roster(url):
    """
    Scrape Bundesliga rosters by team
    """

    response = requests.get(url, headers=HEADERS, timeout=10)
    response.raise_for_status()

    soup = BeautifulSoup(response.text, "html.parser")

    # team name
    title_div = soup.find("div", class_="leg_table_title leg_table_title_inner")
    if not title_div:
        return []

    h2 = title_div.find("h2", class_="text-uppercase")
    if not h2:
        return []

    team_name = h2.get_text(strip=True)

    rows = []

    # players
    for row in soup.find_all("div", class_="leg_column_row"):

        middle_info_box = row.find("div", class_="middle_info_box firstname")
        if not middle_info_box:
            continue

        first_name = middle_info_box.find("span")
        last_name = middle_info_box.find("strong")

        if not first_name or not last_name:
            continue

        player_name = (
            f"{first_name.get_text(strip=True)} "
            f"{last_name.get_text(strip=True)}"
        )

        # sidelined status
        status_text = None
        sidelined_reason = None

        status_div = row.find("div", class_="process_inner_column14 pull-left")
        if status_div:
            img = status_div.find("img")
            if img and img.get("src"):
                status_text = img["src"].split("/")[-1].split(".")[0]
                sidelined_reason = img.get("alt")

        rows.append({
            "li_player_name": player_name,
            "li_team_name": team_name,
            "li_sidelined_status": status_text,
            "li_sidelined_reason": sidelined_reason
        })

    print(rows)
    return rows

#################################################
#   CRAWL ROSTER URLS OF ALL BUNDESLIGA TEAMS   #
#################################################

log_headline("Scraping Bundesliga roster URLs")

BASE_URL = "https://www.ligainsider.de/"
START_URL = "https://www.ligainsider.de/"
HEADERS = {"User-Agent": "Mozilla/5.0"}

log("Scraping URL " + str(START_URL))

response = requests.get(START_URL, timeout=10)
response.raise_for_status()

soup = BeautifulSoup(response.text, "html.parser")

roster_urls = set()

# get the container
icon_holder = soup.find("div", class_="icon_holder")

# find URLs inside container
for a in icon_holder.find_all("a", href=True):
    href = a["href"]

    if href.endswith("/kader/"):
        full_url = urljoin(BASE_URL, href)
        roster_urls.add(full_url)

# convert to list
roster_urls = sorted(roster_urls)

print(roster_urls)
log(f"Found {len(roster_urls)} URLs")

######################################
#   CRAWL INJURY + SUSPENSION DATA   #
######################################

log_headline("Scraping Bundesliga injury and suspension status")

all_rows = []

# crawl rosters of all bundesliga teams
for url in roster_urls:
    team_rows = scrape_bundesliga_roster(url)
    all_rows.extend(team_rows)

df_crawler = pd.DataFrame(all_rows)

# drop players with no sidelined status
df_crawler = df_crawler[
    df_crawler["li_sidelined_status"].notna() &
    (df_crawler["li_sidelined_status"] != "fit")
].reset_index(drop=True)

# data preparation
mapping_status = {
    'verletzung': 'Verletzung',
    'aufbautraining': 'Aufbautraining',
    'verbannung': 'Nicht im Kader',
    'rote-karte': 'Rote Karte',
    'gelb-rote-karte': 'Gelb-Rote Karte',
    'gelbe-karte': '5. Gelbe Karte',
    'angeschlagen-down': 'Schwer Angeschlagen',
    'angeschlagen-up': 'Leicht Angeschlagen',
    'angeschlagen-unsure': 'Angeschlagen'
}

df_crawler['li_sidelined_status'] = df_crawler['li_sidelined_status'].map(mapping_status)

print(df_crawler.head())
print(df_crawler.shape)

log("Created df_crawler with " + str(len(df_crawler)) + " rows")

#######################
#   MAPPING TEAM IDs  #
#######################

log_headline("Mapping team names to team ids")

# Map crawled team names to database ids
log('Create mapping dict')

# List needs to be extended if Bundesliga teams change
mapping_team = {
    'FC Bayern München': 503,
    'Bayer 04 Leverkusen': 3321,
    'Eintracht Frankfurt': 366,
    'Borussia Dortmund': 68,
    'SC Freiburg': 3543,
    '1. FSV Mainz 05': 794,
    'RB Leipzig': 277,
    'SV Werder Bremen': 82,
    'VfB Stuttgart': 3319,
    'Borussia Mönchengladbach': 683,
    'VfL Wolfsburg': 510,
    'FC Augsburg': 90,
    '1. FC Union Berlin': 1079,
    'FC St. Pauli': 353,
    'TSG Hoffenheim': 2726,
    '1. FC Heidenheim': 2831,
    '1. FC Köln': 3320,
    'Hamburger SV': 2708,
}

log('Conducting mapping')
df_crawler['sm_team_id_mapped'] = df_crawler['li_team_name'].map(mapping_team)

#######################################
#   FUZZY MATCH PLAYERS TO DATABASE   #
#######################################

log_headline("Fuzzy match website player names to database player names")

# Clean player names to fuzzy match player names to database player ids
log("Cleaning website player names")

df_crawler['li_player_name_cleaned'] = df_crawler['li_player_name'].apply(clean_name)
df_crawler["li_player_name_cleaned"] = remove_special_chars(df_crawler["li_player_name_cleaned"])

log("Reading active players from databse")

# Connect to database
log('Connecting to database')
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# Read table sm_playerbase
try:
    df_playerbase = pd.read_sql('''
        SELECT 
            id AS sm_player_id
            , fullname AS sm_full_name
            , display_name AS sm_display_name
            , current_team_id AS sm_team_id
        FROM sm_playerbase 
        WHERE rostered = 1
        ''', engine)
    db_message = 'Created df_playerbase with ' + str(len(df_playerbase)) + ' rows'
except:
    db_message = 'Failed to create df_playerbase from database table sm_playerbase'

log(db_message)

df_playerbase["sm_full_name"] = remove_special_chars(df_playerbase["sm_full_name"])
df_playerbase["sm_display_name"] = remove_special_chars(df_playerbase["sm_display_name"])

print(df_playerbase)

# Perform matching
log("Conducting fuzzy matching")

print(df_crawler)

matches = []

for idx, crawler_row in df_crawler.iterrows():
    li_player_name_cleaned = crawler_row['li_player_name_cleaned']
    sm_team_id_mapped = crawler_row['sm_team_id_mapped']

    log("Fuzzy matching for player " + str(li_player_name_cleaned))

    # Filter playerbase by matching team_id
    possible_players = df_playerbase[df_playerbase['sm_team_id'] == sm_team_id_mapped]
    
    found_match = False

    # 1) Primary comparison
    for _, player_row in possible_players.iterrows():
        if name_match(li_player_name_cleaned, player_row["sm_full_name"]):
            matches.append({
                "li_player_name_cleaned": li_player_name_cleaned,
                "sm_team_id_mapped": sm_team_id_mapped,
                "sm_player_fullname": player_row["sm_full_name"],
                "sm_player_id": player_row["sm_player_id"],
            })
            found_match = True

    # 2) Fallback comparison (only if no match found)
    if not found_match:
        for _, player_row in possible_players.iterrows():
            if name_match(li_player_name_cleaned, player_row["sm_display_name"]):
                matches.append({
                    "li_player_name_cleaned": li_player_name_cleaned,
                    "sm_team_id_mapped": sm_team_id_mapped,
                    "sm_player_fullname": player_row["sm_display_name"],
                    "sm_player_id": player_row["sm_player_id"],
                })

# Convert matches to DataFrame
df_matches = pd.DataFrame(matches)
log("Fuzzy matching successfull for " + str(len(df_matches)) + "/" + str(len(df_crawler)) + " players")

# Joining everything together
log("Merging matched players")

df_joined = df_crawler.merge(
    df_matches[['li_player_name_cleaned', 'sm_player_id', 'sm_player_fullname']],
    how='left',
    on='li_player_name_cleaned'
)

#################################
#   WRITE RESULTS TO DATABASE   #
#################################

log_headline("Writing results to database")

# Connect to database
log('Connecting to database')

from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  

# Define target columns
target_columns = [
    "li_player_name",
    "li_player_name_cleaned",
    "li_team_name",
    "li_sidelined_status",
    "li_sidelined_reason",
    "sm_player_fullname",
    "sm_player_id",
    "sm_team_id_mapped",
]

# Create target dataframe matching database table
df_insert = df_joined[target_columns].copy()
df_insert = df_insert.rename(columns={"sm_team_id_mapped": "sm_team_id"})

# Write results to database
with engine.connect() as con:
    # Truncate
    log('Truncate table li_sidelined_players')
    con.execute("TRUNCATE TABLE li_sidelined_players")

    # Insert
    log('Insert into table li_sidelined_players')
    df_insert.to_sql(
        name='li_sidelined_players',
        con=con,
        if_exists="append",
        index=False
    )