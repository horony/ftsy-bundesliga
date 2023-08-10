#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Fri Jul  7 13:41:51 2023

Scprit to orchestrate updating fast changing dimension tables sm_rounds and sm_fixtures (for goals etc. during a match day)

@author: lennart
"""

import os
import sys

sys.path.insert(2, '../py/')
from logging_function import log

print("")
print("###############################")
print("#  RUNNING ORCHESTRATION FCD  #")
print("###############################")
print("")


print("")
print("##################")
print("#  SM_ROUNDS.PY  #")
print("##################")
print("")

log('Calling script 1/2 - sm_rounds.py')
os.system('python3.6 sm_rounds.py')


print("")
print("####################")
print("#  SM_FIXTURES.PY  #")
print("####################")
print("")

os.system('python3.6 sm_fixtures.py')
log('Calling script 2/2 - sm_fixtures.py')