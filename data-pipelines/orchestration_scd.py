#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Thu Jun 11 12:23:12 2020

Scprit to orchestrate updating slowly changing dimension tables

@author: lennart
"""

import os
import sys

sys.path.insert(2, '../py/')
from logging_function import log

print("")
print("###############################")
print("#  RUNNING ORCHESTRATION SCD  #")
print("###############################")
print("")

print("")
print("###################")
print("#  SM_SEASONS.PY  #")
print("###################")
print("")

log('Calling script 1/6 - sm_seasons.py')
os.system('python3.6 sm_seasons.py')

print("")
print("##################")
print("#  SM_ROUNDS.PY  #")
print("##################")
print("")

log('Calling script 2/6 - sm_rounds.py')
os.system('python3.6 sm_rounds.py')

print("")
print("####################")
print("#  SM_FIXTURES.PY  #")
print("####################")
print("")

log('Calling script 3/6 - sm_fixtures.py')
os.system('python3.6 sm_fixtures.py')

print("")
print("##################")
print("#  SM_VENUES.PY  #")
print("##################")
print("")

log('Calling script 4/6 - sm_venues.py')
os.system('python3.6 sm_venues.py')

print("")
print("#################")
print("#  SM_TEAMS.PY  #")
print("#################")
print("")

log('Calling script 5/6 - sm_teams.py')
os.system('python3.6 sm_teams.py')

print("")
print("######################")
print("#  SM_PLAYERBASE.PY  #")
print("######################")
print("")

log('Calling script 6/6 - sm_playerbase.py')
os.system('python3.6 sm_playerbase.py')