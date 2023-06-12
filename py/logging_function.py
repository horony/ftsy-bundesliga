#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Mon Jun 12 19:32:10 2023

"""

from datetime import datetime

# Generic functions for logging
def current_time():
    """
    Return current time in hh:mm:ss
    """
    current_time = datetime.now().strftime("%H:%M:%S")
    return(current_time)

def log(message):
    """
    Prints message with timestamp for logging
    """
    return(print(current_time(), '-', str(message)))

def log_headline(message):
    """
    Prints headline message for logging
    """
    return(print('\n>>> ', message.upper(), '<<<\n'))