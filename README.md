# ftsy-bundesliga

*Fantasy Bundesliga* is a private fantasy soccer manager game for Bundesliga. Around 2018 a friend and me grew tired from the likes of *Comunio* or 
*Kickbase* while at the same time discovering NFL Fantasy managers. By developing *Fantasy Bundesliga* we tried to translate the game mode of NFL Fantasy (draft, live scores, waivers, head-to-heads, etc.) to the Bundesliga world. At first in Google sheets solution. Later with this small website project.

## Features

*Fantasy Bundesliga* contains most features known from regular NFL fantasy managers. Starting from a live draft, transfer system with waivers and trades, near-realtime scoring on match days and extensive player statistics.

**Examples:**
Formation page           |  Home page         |
:-------------------------:|:-------------------------:|
![](/documentation/ftsy-buli-screenshot-aufstellung.png)  |  ![](/documentation/ftsy-buli-screenshot-home.png)

**List of features:**
* Live draft with 10-12 users
* Each season features a league mode and a cup mode
* Head-to-head matchups each match day playing out league and cup
* Near-realtime scoring on each match day
* Transfer system with waivers and trades
* Extensive stats that users can harness to research player and user performance
* Player and Bundesliga news to keep users informed within the website

## Architecture

The **frontend** is a - I suppose - typical website frontend (*HTML* + *CSS* + *JavaScript* + *PHP*). Heart of the *backend* is a small **MySQL Database**. The database is fueld by a number of *Python* ETLs. These **external data-pipelines* are essentially 1.) live scoring data pulled from a data providers API (see */data-pipelines/sm_player_stats.py*), 2.) daily updates on dimension tables data pulled from a data providers API (see */data-pipelines/sm_dim_table_update.py*) 3.) daily Bundesliga news updates through web scraping (see */data-pipelines/soccer-news-crawler.py*). In addition to the external data-pipelines two types of **internal data-pipelines** are in use: Firstly after each Bundesliga round data is reaggregated to calculate standings and statisitics (see */data-pipelines/spieltag-abschluss.php*) and scripts. Secondly a number of scripts and jobs triggerd by user interaction (see */php/jobs/*).

**Architecture sketch:**
![Architecture sketch of Fantasy Bundesliga](/documentation/ftsy-buli-architecture-sketch.png)
