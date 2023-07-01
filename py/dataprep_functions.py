#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Created on Sat Jul  1 15:13:29 2023

@author: lennart
"""

def isNone(value,replacement):
    if value is None:
        return replacement
    else:
        return value