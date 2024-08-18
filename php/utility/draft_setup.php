<?php
	
	include('../auth.php');
	include("../../secrets/mysql_db_connection.php");

	/* Query number of draft rounds */

	$total_rounds = mysqli_query($con, "SELECT draft_rounds FROM xa7580_db1.draft_meta WHERE league_id = 1 ") -> fetch_object() -> draft_rounds;

	/* Truncate draft_order and insert data from past season */

	mysqli_query($con, "TRUNCATE TABLE xa7580_db1.draft_order");
	mysqli_query($con, "                                   
		INSERT INTO draft_order

		SELECT 	tab.league_id
		        , usr.id
		        , usr.username
		        , usr.teamname
		        , case 	when tab.rang = 1 then 10 
		                when tab.rang = 2 then 9
		                when tab.rang = 3 then 8
		                when tab.rang = 4 then 7
		                when tab.rang = 5 then 6
		                when tab.rang = 6 then 5
		                when tab.rang = 7 then 4
		                when tab.rang = 8 then 3
		                when tab.rang = 9 then 2
		                when tab.rang = 10 then 1
		                end as drft_pos

		FROM 	  `ftsy_tabelle_2020` tab

		INNER JOIN users usr
			ON 	tab.player_id = usr.id

		/**************************************/
		/* EDIT SEASON AND SPIELTAG BELOW !!! */
		/**************************************/

		WHERE tab.spieltag = 34 AND tab.season_id = 21795

		ORDER BY tab.rang DESC
   	");
   	
  /* Truncate draft_order_full and repopulate it trough draft_order */
	
	mysqli_query($con, "TRUNCATE TABLE xa7580_db1.draft_order_full");

  /* Loop over draft rounds */
  
  $round_to_write = 1;
	while($round_to_write <= $total_rounds){
		mysqli_query($con,"
			INSERT INTO xa7580_db1.draft_order_full (league_id, user_id, username, teamname, rank, round, pick, player_id, player_name)
			SELECT a.league_id, a.user_id, a.username, a.teamname, a.rank, a.rnd as round, null as pick, null as player_id, null as player_name
			FROM (
        SELECT *
        FROM  ( SELECT '".$round_to_write."' AS rnd ) AS round
        CROSS JOIN
         	( SELECT * FROM xa7580_db1.draft_order ) AS ord
        ) a
      ORDER BY round desc, CASE WHEN (round % 2) > 0 THEN rank END ASC, CASE WHEN (round % 2) = 0 THEN rank END DESC
		");
		$round_to_write = $round_to_write + 1;
	}
	
	/* Overwrite the ranking trough an update */
	
	mysqli_query($con, "SET @r=0");
	mysqli_query($con, "UPDATE xa7580_db1.draft_order_full SET pick = @r:= (@r+1)");

	/* Update table draft_player_base containing all draft eligable players */
	
	mysqli_query($con, "DROP TABLE xa7580_db1.draft_player_base");

	mysqli_query($con, "
		CREATE TABLE `draft_player_base` AS
		SELECT	1 as ftsy_league_id
			, base.id
			, base.display_name
		        , base.common_name
		        , teams.name as teamname
		        , teams.short_code as teamname_code
		        , teams.logo_path as team_logo
		        , base.lastname
		        , base.position_short
		        , base.position_long
		        , base.image_path
		        , base.is_sidelined
		        , base.is_suspended
		        , base.injured
		        , base.injury_reason
			, null as pick 
			, null as round
			, null as pick_by
			, null as autopick_custom_list_flg
			, null as autopick_ranking_flg
			, null as pick_ts
			, null as league_id
			, 23744 as season_id # HERE NEW SEASON !!! 

		FROM 	sm_playerbase base

		INNER JOIN sm_teams teams
			ON teams.id = base.current_team_id

		WHERE	rostered = 1
		");

	mysqli_query($con, "ALTER TABLE draft_player_base MODIFY ftsy_league_id INTEGER;");
	mysqli_query($con, "ALTER TABLE draft_player_base MODIFY pick INTEGER;");
	mysqli_query($con, "ALTER TABLE draft_player_base MODIFY round INTEGER;");
	mysqli_query($con, "ALTER TABLE draft_player_base MODIFY pick_by INTEGER;");
	mysqli_query($con, "ALTER TABLE draft_player_base MODIFY autopick_custom_list_flg INTEGER;");
	mysqli_query($con, "ALTER TABLE draft_player_base MODIFY autopick_ranking_flg INTEGER;");
	mysqli_query($con, "ALTER TABLE draft_player_base MODIFY pick_ts DATETIME;");
	mysqli_query($con, "ALTER TABLE draft_player_base MODIFY league_id INTEGER;");
	mysqli_query($con, "ALTER TABLE draft_player_base MODIFY season_id INTEGER;");
	
	/* Reset meta table */
	
	mysqli_query($con, "
		UPDATE draft_meta
		SET draft_complete_flg = 0, draft_status = 'open', current_pick_no = 1, current_round = 1, on_the_clock = (SELECT teamname from draft_order_full where pick = 1)
		WHERE league_id = 1
		");
		
?>
