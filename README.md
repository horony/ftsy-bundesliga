# ftsy-bundesliga

*Fantasy Bundesliga* is a private fantasy soccer manager game for Bundesliga. Around 2018 a friend and me grew tired from the likes of *Comunio* & Co. while at the same time discovering NFL Fantasy managers. By developing *Fantasy Bundesliga* we tried to translate the game mode of NFL Fantasy to the Bundesliga world. At first with an impromptu Google sheets + Web Crawler solution. By the 2020 we left Google sheets behind us and switched over to this small website project.

## Features

*Fantasy Bundesliga* contains most features known from regular NFL fantasy managers. Starting from a live draft, transfer system with waivers and trades, near-realtime scoring up to extensive player statistics.

**Examples:**
Formation page           |  Home page         |
:-------------------------:|:-------------------------:|
![](/documentation/ftsy-buli-screenshot-aufstellung.png)  |  ![](/documentation/ftsy-buli-screenshot-home.png)

**List of features:**
* Live draft with 10-12 users.
* Each season features a league mode and a cup mode.
* Head-to-head between users each round counting towards league standings or cup progress.
* Near-realtime scoring for each fixture and each round.
* Transfer system with waivers, trades and free agency.
* Extensive stats that users can harness to research player statistics and optimize user performance (e.g. which player attempts the most crosses? Is attempting tacklings or completing passes more important for a good fantasy performance?).
* Player projections for the upcoming fixture.
* Player and Bundesliga news to keep users informed within the website (e.g. real world injuries or transfers).

## Architecture

The **frontend** is a - I suppose - typical website frontend (*HTML* + *CSS* + *JavaScript* + *PHP*). Heart of the **backend** is a small *MySQL Database*. 

The database is fueld by a number of *Python* ETLs. These **external data-pipelines** are essentially 1.) live scoring data pulled from a data providers API (see */data-pipelines/sm_player_stats.py*), 2.) daily updates on dimension tables data pulled from the same data providers API (see */data-pipelines/sm_dim_table_update.py*) and 3.) daily Bundesliga news updates collected by web scraping (see */data-pipelines/soccer-news-crawler.py*). 

In addition to the external data-pipelines two types of **internal data-pipelines** are in use: Firstly after each Bundesliga round data is reaggregated to calculate standings and statisitics (see */data-pipelines/spieltag-abschluss.php*) and scripts. Secondly a number of scripts and jobs triggerd by user interaction (see */php/jobs/*).

**Architecture sketch:**

![](/documentation/ftsy-buli-architecture-sketch.png)
[<img alt="Deployed with FTP Deploy Action" src="https://img.shields.io/badge/Deployed With-FTP DEPLOY ACTION-%3CCOLOR%3E?style=for-the-badge&color=2b9348">](https://github.com/SamKirkland/FTP-Deploy-Action)
