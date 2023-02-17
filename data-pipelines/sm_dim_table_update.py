#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Thu Jun 11 12:23:12 2020

Orchestration script updating different dimension tables.

@author: lennart
"""

import os

print("\n>> Update SportMonks dimension tables <<<\n")

# SEASONS
print('\n>>> Running sm_seasons.py <<<\n')
os.system('python3.6 sm_seasons.py')

# FIXTURES AND ROUNDS
print('\n>>> Running sm_fixtures.py <<<\n')
os.system('python3.6 sm_fixtures.py')

# VENUES
print('\n>>> Running sm_venues.py <<<\n')
os.system('python3.6 sm_venues.py')

# TEAMS
print('\n>>> Running sm_teams.py <<<\n')
os.system('python3.6 sm_teams.py')

# SQUADS AND PLAYERBASE
print('\n>>> Running sm_playerbase.py <<<\n')
os.system('python3.6 sm_playerbase.py')
