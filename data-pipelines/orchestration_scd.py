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

log('Calling script 1/5 - sm_seasons.py')

print("")
print("###################")
print("#  SM_SEASONS.PY  #")
print("###################")
print("")

os.system('python3.6 sm_seasons.py')

log('Calling script 2/5 - sm_fixtures.py')

print("")
print("####################")
print("#  SM_FIXTURES.PY  #")
print("####################")
print("")

os.system('python3.6 sm_fixtures.py')

log('Calling script 3/5 - sm_venues.py')

print("")
print("##################")
print("#  SM_VENUES.PY  #")
print("##################")
print("")

os.system('python3.6 sm_venues.py')

log('Calling script 4/5 - sm_teams.py')

print("")
print("#################")
print("#  SM_TEAMS.PY  #")
print("#################")
print("")

os.system('python3.6 sm_teams.py')

log('Calling script 5/5 - sm_playerbase.py')

print("")
print("######################")
print("#  SM_PLAYERBASE.PY  #")
print("######################")
print("")

os.system('python3.6 sm_playerbase.py')