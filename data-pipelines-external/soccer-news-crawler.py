#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""

Created on Sun Dec 20 12:28:49 2020

@author: lennart

Crawler extracting soccer player news (e.g. transfers or injuries from a news site and writes them into a database.
                                       
"""

# Import packages
import requests
import sys
sys.path.insert(1, './secrets/')
from bs4 import BeautifulSoup
import pandas as pd 
from sqlalchemy import create_engine

"""
Connect to news website and extract HTML
"""

headers = { 'user-agent': 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0' }
url = ("https://www.ligainsider.de/")
r  = requests.get(url, headers=headers)

data = r.text
soup = BeautifulSoup(data, 'html.parser')

"""
Parse extracted HTML
"""

# Setup dummy lists
news_headline = []
news_player = []
news_link = []
news_top = []

# News are usually saved in html element 'newsboxlink'
for raw in soup.find_all("a", class_="newsboxlink", href=True):  
       
    # Find headlines and append to news_headline
    for title in raw.find_all('h3'):
        news_headline.append(title.text)
    
    # Find links connected to headlines and append to news_link
    news_link.append('https://www.ligainsider.de' + raw['href'])   
    
    # Check if news is classified as top-news and append to news_top
    is_topnews = raw.parent.parent.parent.parent.find('img', title='TopNews')
    if is_topnews is not None:
        news_top.append('yes')
    else:
        news_top.append('no')
        
    # Check if news is a player-news and append to news_player
    is_playernews = raw.parent.find('a', class_='profile_link_box').text
    if is_playernews is not None:
        news_player.append(is_playernews)
    else:
        news_player.append(None)
        
# Quality check if all lists have same length
if len(news_link) != len(news_headline):
    raise ValueError('Number of elements in news and headlines are not matching.')

# Create pandas dataframe from the lists
news_df = pd.DataFrame(
    {'headline': news_headline,
     'li_link': news_link,
     'player': news_player,
     'is_topnews': news_top
    })
news_df['load_ts'] = pd.to_datetime('now')
print(news_df.head())

"""
Connect to MySQL-database and write news into table
"""

# Connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)


# Create table news_ligainsider if it does not exist yet
try:
    # Create table
    news_df.to_sql(name='news_ligainsider', con=engine, index=True, if_exists='fail')
    sql_message = 'Table news_ligainsider created'

# If the table already exist update it through a temporary table
except:
    # Create temp table
    news_df.to_sql(name='news_ligainsider_tmp', con=engine, index=False, if_exists='replace')
    with engine.connect() as con:
        
        # Delete potential duplicates in temp
        con.execute('''
                    DELETE 
                    FROM    news_ligainsider_tmp
                    WHERE   headline IN (SELECT headline FROM news_ligainsider)
                            AND li_link IN (SELECT li_link FROM news_ligainsider)
                    ;''')    
        
        # Insert into news_ligainsider from temp
        con.execute('''
                    INSERT INTO news_ligainsider 
                        SELECT  null, headline, li_link, player, is_topnews, load_ts
                        FROM    news_ligainsider_tmp
                    ;''')  
        
        # Insert into top-news from news_ligainsider
        con.execute('''
                    INSERT INTO news 
                        SELECT  null, null, null, headline, 'LigaInsider-Crawler', load_ts, null, null, li_link, null, 'li_news'
                        FROM    news_ligainsider_tmp
                        WHERE   is_topnews = 'yes'
                    ;''')    
        
        # Cleanup: Drop temp table
        con.execute('DROP TABLE news_ligainsider_tmp;')    

    sql_message = "Table news_ligainsider updated!"
  
print(sql_message)