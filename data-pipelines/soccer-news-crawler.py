#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""

Created on Sun Dec 20 12:28:49 2020
Updated on Tue Jul 18 22:12:09 2023

@author: lennart

Crawler extracting soccer player news (e.g. transfers or injuries from a news site and writes them into a database.
                                       
"""

import sys
sys.path.insert(1, '../secrets/')

sys.path.insert(2, '../py/')
from logging_function import log, log_headline

import requests
import sys
from bs4 import BeautifulSoup
import pandas as pd 
from sqlalchemy import create_engine

############################
#   EXTRACT + PARSE DATA   #
############################

log_headline('(1/2) EXTRACT NEWS DATA FROM LIGAINSIDER')

# crawler set up
headers = { 'user-agent': 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:64.0) Gecko/20100101 Firefox/64.0' }

# crawl data from main page
log('Extract data from ligainsider.de main page')
url_website = ('https://www.ligainsider.de/')
r_website  = requests.get(url_website, headers=headers)

data_website = r_website.text
soup_website = BeautifulSoup(data_website, 'html.parser')

# setup empty lists to collect data
news_headline = []
news_player = []
news_link = []
news_top = []

log('Parse news data from the extracted data')

# news are usually saved in html element 'newsboxlink'
for soup_news in soup_website.find_all("a", class_="newsboxlink", href=True):  
       
    # find headlines and append to news_headline
    for title in soup_news.find_all('h3'):
        news_headline.append(title.text)
    
    # find links connected to headlines and append to news_link
    news_link.append('https://www.ligainsider.de' + soup_news['href'])   
    
    # check if news is classified as top-news and append to news_top
    is_topnews = soup_news.parent.parent.parent.parent.find('img', title='TopNews')
    if is_topnews is not None:
        news_top.append('yes')
    else:
        news_top.append('no')
        
    # check if news is a player-news and append to news_player
    is_playernews = soup_news.parent.find('a', class_='profile_link_box').text
    if is_playernews is not None:
        news_player.append(is_playernews)
    else:
        news_player.append(None)
        
# Quality check if all lists have same length
log('Quality check: Check if parsed data is matching')
if len(news_link) != len(news_headline):
    raise ValueError('Number of elements in news and headlines are not matching.')

# create pandas dataframe from the lists
log('Building DataFrame from parsed html data')
df_news = pd.DataFrame(
    {'headline': news_headline,
     'li_link': news_link,
     'player': news_player,
     'is_topnews': news_top
    })
df_news['load_ts'] = pd.to_datetime('now')
log('Created DataFrame containing ' + str(df_news.shape[0]) + ' news')

##########################
#   WRITE INTO DATABASE  #
##########################

log_headline('(2/2) WRITE INTO DATABASE')
log('Connecting to MySQL database')     

# connect to MySQL-database
from mysql_db_connection import db_user, db_pass, db_port, db_name
engine = create_engine('mysql+mysqlconnector://'+db_user+':'+db_pass+'@localhost:'+db_port+'/'+db_name, echo=False)  


# create table news_ligainsider if it does not exist yet
try:
    # Create table
    df_news.to_sql(name='news_ligainsider', con=engine, index=True, if_exists='fail')
    sql_message = 'Table news_ligainsider created'

# if the table already exist update it through a temporary table
except:
    
    # create temp table
    df_news.to_sql(name='news_ligainsider_tmp', con=engine, index=False, if_exists='replace')
    
    with engine.connect() as con:
        
        # delete potential duplicates in temp
        con.execute('''
                    DELETE 
                    FROM    news_ligainsider_tmp
                    WHERE   headline IN (SELECT headline FROM news_ligainsider)
                            AND li_link IN (SELECT li_link FROM news_ligainsider)
                    ;''')    
        
        # insert new news into news_ligainsider from temp
        con.execute('''
                    INSERT INTO news_ligainsider 
                        SELECT  null, headline, li_link, player, is_topnews, load_ts
                        FROM    news_ligainsider_tmp
                    ;''')  
        
        # insert into top-news from news_ligainsider
        con.execute('''
                    INSERT INTO news 
                        SELECT  null, null, null, headline, 'LigaInsider-Crawler', load_ts, null, null, li_link, null, 'li_news'
                        FROM    news_ligainsider_tmp
                        WHERE   is_topnews = 'yes'
                    ;''')    
        
        # cleanup: Drop temp table
        con.execute('DROP TABLE news_ligainsider_tmp;')    

    sql_message = "Table news_ligainsider updated!"
  
log(sql_message)