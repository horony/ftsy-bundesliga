<?php

/************************************************/
/* SCRIPT TO SWITCH SPIELTAG / ROUND						*/
/*																							*/
/*  [1] Historize scoring_akt in scoring_hist		*/
/*  [2] Update scoring_snap 										*/
/*  [3] Update ftsy match results								*/
/*  [4] Update ftsy standings										*/
/*  [5] Update waiver priorities								*/
/*  [6] Update parameters												*/
/*	[7] Update points allowed										*/
/*  [8] Update topxi														*/
/*	[9] Write News															*/
/*																							*/
/************************************************/

include('../php/auth.php');
include("../secrets/mysql_db_connection.php");

// Preparations: Get meta-data

echo "0/9 - Fetching meta data from MySQL DB" . "<br>";

// Current spieltag name
$aktueller_spieltag = mysqli_query($con, "
	SELECT 	spieltag 
	FROM 		xa7580_db1.parameter
	") -> fetch_object() -> spieltag;

// Current spieltag type (cup or league)
$aktueller_spieltag_type = mysqli_query($con, "
	SELECT 	match_type 
	FROM 		xa7580_db1.ftsy_schedule 
	WHERE 	buli_round_name = (SELECT spieltag FROM xa7580_db1.parameter) 
					AND season_id = (SELECT season_id FROM xa7580_db1.parameter) 
	LIMIT 1
	") -> fetch_object() -> match_type;

// Current season_id
$akt_season_id = mysqli_query($con, "
	SELECT 	season_id 
	FROM 		xa7580_db1.parameter
	") -> fetch_object() -> season_id;

// Current round_id
$akt_round_id = mysqli_query($con, "
	SELECT 	id 
	FROM 		xa7580_db1.sm_rounds 
	WHERE 	name = '$aktueller_spieltag' 
					AND season_id = '$akt_season_id'
	") -> fetch_object() -> id;

/***********************************************/
/* [1] Historize scoring_akt into scoring_hist */
/***********************************************/

echo "1/9 - Historize scoring_akt into scoring_hist" . "<br>";

// Delete from ftsy_scoring_hist, to prevent doubled entries

mysqli_query($con, "
	DELETE FROM xa7580_db1.ftsy_scoring_hist 
	WHERE ( round_name = '".$aktueller_spieltag."' AND season_id = '".$akt_season_id."' ) OR round_name IS NULL 
	");

// Insert from ftsy_scoring_akt into ftsy_scoring_hist
$insert_akt_into_hist_sql = file_get_contents('../sql/snippets/ftsy-sa-insert-akt-into-hist.sql');
mysqli_query($con,	$insert_akt_into_hist_sql);

sleep(10);

/****************************/
/* [2] Update ftsy snapshot */
/****************************/

echo "2/9 - Update player snapshot" . "<br>";

// Delete old snapshot and create new snapshot with the updated data from ftsy_scoring_hist
include 'create-ftsy-snapshot.php';

sleep(10);

/****************************/
/* [3] Update ftsy schedule */
/****************************/

echo "3/9 - Update ftsy schedule" . "<br>";

// Option to exclude rounds
if ($aktueller_spieltag != 13) {

	$update_schedule_sql = file_get_contents('../sql/snippets/ftsy-sa-update-ftsy-schedule.sql');
	mysqli_query($con, $update_schedule_sql);

}

sleep(10);

/*****************************/
/* [4] Update ftsy standings */
/*****************************/

echo "4/9 - Update ftsy standings" . "<br>";

// League
if ($aktueller_spieltag_type == 'league'){

	// Option to exclude rounds
	if ($aktueller_spieltag != 13){

		// Delete old data if exists
		mysqli_query($con, "
			DELETE 
			FROM xa7580_db1.ftsy_tabelle_2020 
			WHERE 	spieltag = '".$aktueller_spieltag."' 
							AND season_id = '".$akt_season_id."' 
							AND league_id = 1 
			");

		// Insert new round
		$update_league_standings_sql = file_get_contents('../sql/snippets/ftsy-sa-update-ftsy-league-standings.sql');
		mysqli_query($con, $update_league_standings_sql);

		// Update Ranking
		$update_league_ranking_sql = file_get_contents('../sql/snippets/ftsy-sa-update-ftsy-league-ranking.sql');		
		mysqli_query($con, $update_league_ranking_sql);
		
		// Update up-down-markers based on updated data
		$update_league_movement_sql = file_get_contents('../sql/snippets/ftsy-sa-update-ftsy-league-standings-movement.sql');
		mysqli_query($con, $update_league_movement_sql);
	
	}

	/*****************************/
	/* [5] Update waiver ranking */
	/*****************************/

	echo "5/9 - Update waiver ranking" . "<br>";

	// Update waiver ranking
	$update_waiver_sql = file_get_contents('../sql/snippets/ftsy-sa-update-waiver-ranking.sql');
	mysqli_query($con, $update_waiver_sql );

}

/*************************/
/* [6] Update parameters */
/*************************/

echo "6/9 - Update game parameters" . "<br>";

// Define new round name
mysqli_query($con, "UPDATE xa7580_db1.parameter SET spieltag = '".$aktueller_spieltag."' + 1");

// Define new waiver dates
$update_waiver_dates_sql = file_get_contents('../sql/snippets/ftsy-sa-update-waiver-dates.sql');
mysqli_query($con, $update_waiver_dates_sql);

/*****************************/
/* [7] Update points allowed */
/*****************************/

echo "7/9 - Update points allowed table" . "<br>";

// Drop and recreate the ftsy_points_allowd table

mysqli_query($con,"DROP TABLE xa7580_db1.ftsy_points_allowed");

$create_points_allowed_sql = file_get_contents('../sql/snippets/ftsy-sa-create-points-allowed.sql');
mysqli_query($con, $create_points_allowed_sql		);

/********************/
/* [8] Update topxi */
/********************/

echo "8/9 - Update topxi" . "<br>";

// Call py-script to drop and recreate the topxi-tables

$shellcommand = escapeshellcmd('/home/www/data-pipelines/topxi.py');
shell_exec($shellcommand);

/**************************/
/* [9] Write news to page */
/**************************/

echo "9/9 - Write news" . "<br>";

$story = <<<EOT
Spieltag $aktueller_spieltag abgeschlossen!
EOT;
$headline = 'Spieltag abgeschlossen';
mysqli_query($con, "INSERT INTO xa7580_db1.news(name, headline, story, `timestamp`, add_id, drop_id, add_besitzer, drop_besitzer, type) VALUES('System', '".utf8_decode($headline)."', '".utf8_decode($story)."', NOW(), '', '','','','spieltag_abschluss' )");

?>