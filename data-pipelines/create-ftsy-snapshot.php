<?php
include('../php/auth.php');
include("../secrets/mysql_db_connection.php");

// Drop old snapshot
mysqli_query($con,"	DROP TABLE ftsy_scoring_snap");

// Collect meta data
$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
$akt_spieltag = mysqli_query($con, "SELECT spieltag FROM xa7580_db1.parameter ") -> fetch_object() -> spieltag; 
$akt_season_id = mysqli_query($con, "SELECT season_id FROM xa7580_db1.parameter ") -> fetch_object() -> season_id;  

// Ensure that the current spieltag does not exceed the max available spieltag im ftsy_scoring_hist
$max_spieltag = mysqli_query($con, "SELECT MAX(round_name) AS max_spieltag FROM xa7580_db1.ftsy_scoring_hist WHERE season_id = '".$akt_season_id."'") -> fetch_object() -> max_spieltag; 
if ($akt_spieltag > $max_spieltag){
    $akt_spieltag = $max_spieltag;
}

/*******************/
/* COLUMN HANDLING */
/*******************/

// Columns that need to be calculated new

$to_calc = mysqli_query($con,"
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS  
    WHERE 
        TABLE_SCHEMA = 'xa7580_db1' 
        AND TABLE_NAME = 'ftsy_scoring_hist'
        AND ( INSTR(COLUMN_NAME, 'stat') > 0 OR INSTR(COLUMN_NAME, 'ftsy') > 0 ) 
        AND ( INSTR(COLUMN_NAME, '1') = 0 )
        AND ( INSTR(COLUMN_NAME, '2') = 0 )
    ");

// Constant columns

$to_keep = mysqli_query($con,"	
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS  
    WHERE 
        TABLE_SCHEMA = 'xa7580_db1' 
        AND TABLE_NAME = 'sm_playerbase' 
    ");

// Columns to be ranked

$to_rank = mysqli_query($con,"	
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS  
    WHERE TABLE_SCHEMA = 'xa7580_db1' 
        AND TABLE_NAME = 'ftsy_scoring_hist'
        AND ( INSTR(COLUMN_NAME, 'stat') > 0 OR INSTR(COLUMN_NAME, 'ftsy_score') > 0 ) 
        AND ( INSTR(COLUMN_NAME, '1') = 0 )
        AND ( INSTR(COLUMN_NAME, '2') = 0 )
        AND COLUMN_NAME IN ('ftsy_score')
    ");

/************************************/
/* CONSTRUCT CREATE TABLE STATEMENT */
/************************************/

$sql_query_complete = "create table ftsy_scoring_snap as select ";
$sql_part_unchanged = "";
$sql_part_sum_avg = "";

// Constant columns

while($row = mysqli_fetch_array($to_keep)){
    $sql_part_unchanged = $sql_part_unchanged . ', base.' . $row['COLUMN_NAME'];
    }

$sql_part_unchanged = substr($sql_part_unchanged, 1);
$sql_query_complete = $sql_query_complete . $sql_part_unchanged;

// Columns to be ranked

$sql_part_rank = '';
$sql_rank_join = '';

while( ($row = mysqli_fetch_array($to_rank)) ){
        $sql_part_alias = 'to_join_' . $row['COLUMN_NAME'];
        $sql_part_rank = ', ' . $sql_part_alias . '.' . $row['COLUMN_NAME'] . '_rank_all, ' . $sql_part_alias . "." . $row['COLUMN_NAME'] . '_rank_pos';

        $sql_rank_join_part = ' LEFT JOIN (
                                SELECT 
                                    a.id
                                    , @curRank := @curRank + 1 AS ' . $row['COLUMN_NAME'] . '_rank_all
                                    , CASE  
                                        WHEN a.position_short = "TW" THEN @curRank_TW := @curRank_TW+1 
                                        WHEN a.position_short = "AW" THEN @curRank_AW := @curRank_AW+1 
                                        WHEN a.position_short = "MF" THEN @curRank_MF := @curRank_MF+1 
                                        WHEN a.position_short = "ST" THEN @curRank_ST := @curRank_ST+1 
                                        END as ' . $row['COLUMN_NAME'] . '_rank_pos
                                FROM (
                                    SELECT 
                                        base.id
                                        , base.position_short
                                        , SUM('.$row['COLUMN_NAME'].') as sum_column
                                    FROM xa7580_db1.sm_playerbase base 
                                    INNER JOIN ftsy_scoring_hist hist 
                                        ON hist.player_id = base.id
                                        AND hist.season_id = ' . $akt_season_id . '
                                    GROUP BY base.id, base.position_short
                                    ORDER BY sum_column desc
                                ) a
                                ,  (SELECT @curRank := 0) rnk
                                ,  (SELECT @curRank_TW := 0) rnk_tw
                                ,  (SELECT @curRank_AW := 0) rnk_aw
                                ,  (SELECT @curRank_MF := 0) rnk_mf
                                ,  (SELECT @curRank_ST := 0) rnk_st
                               ) ' . $sql_part_alias .
                               ' ON '. $sql_part_alias .'.id = base.id ';

    $sql_rank_join = $sql_rank_join . $sql_rank_join_part;
    }

$sql_query_complete = $sql_query_complete . $sql_part_rank;

// Columns that need to be calculated new

$sql_sum_avg = '';

while($row = mysqli_fetch_array($to_calc)){
    $sql_part_sum_avg = ', SUM(coalesce(hist.' .$row['COLUMN_NAME'] . ',0)) as ' . $row['COLUMN_NAME'] . '_sum, round(avg(case when hist.appearance_stat = 1 then hist.' . $row['COLUMN_NAME'] . ' else null end),1) as ' . $row['COLUMN_NAME'] . '_avg , sum(case when hist.round_name = '.$akt_spieltag.' then hist.' . $row['COLUMN_NAME'] . ' else null end ) as ' . $row['COLUMN_NAME'] . '_last, round(avg(case when (hist.round_name between '.$akt_spieltag.'-2 and '.$akt_spieltag.') and hist.appearance_stat = 1 then hist.' . $row['COLUMN_NAME'] . ' else null end),1) as ' . $row['COLUMN_NAME'] . '_avg_last_3, round(avg(case when (hist.round_name between '.$akt_spieltag.'-4 and '.$akt_spieltag.') and hist.appearance_stat = 1 then hist.' . $row['COLUMN_NAME'] . ' else null end),1) as ' . $row['COLUMN_NAME'] . '_avg_last_5';
    $sql_sum_avg =  $sql_sum_avg . $sql_part_sum_avg;
    }

$sql_query_complete = $sql_query_complete . $sql_sum_avg;

// Add FROM part
$sql_query_complete = $sql_query_complete . ' FROM xa7580_db1.sm_playerbase base 
                                              INNER JOIN ftsy_scoring_hist hist 
                                                  ON hist.player_id = base.id
                                                  AND hist.season_id = ' . $akt_season_id;

$sql_query_complete = $sql_query_complete . $sql_rank_join;
$sql_query_complete = $sql_query_complete . ' group by ' . $sql_part_unchanged; 

//echo $sql_query_complete;

if(mysqli_query($con, $sql_query_complete)){  
    echo "Creation of table ftsy_scoring_snap successful";  
} else {  
    echo "Creation of table ftsy_scoring_snap failed";  
}  
?>