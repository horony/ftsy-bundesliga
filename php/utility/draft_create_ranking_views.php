<?php

include("../../secrets/mysql_db_connection.php");


$year = '2024';
$season_id = '23744'; 


$relevant_stat_array = ['ftsy_score', 'minutes_played_stat', 'goals_total_stat', 'pen_scored_stat', 'assists_stat', 'dribbles_success_stat', 'shots_total_stat', 'crosses_total_stat', 'passes_complete_stat', 'key_passes_stat', 'blocks_stat', 'clearances_stat', 'interceptions_stat', 'tackles_stat', 'duels_won_stat', 'clean_sheet_stat', 'pen_saved_stat', 'saves_stat'];

foreach ($relevant_stat_array as &$stat)  {
    
    $view_name = 'rank_' . $stat . '_' . $year;
    echo 'Creating: ' . $view_name;

    mysqli_query($con, "DROP TABLE IF EXISTS `".$view_name."` ");

    mysqli_query($con, "create temporary table `".$view_name."` as

        select  
            base.player_id
            , base.display_name
            , base.position_short
            , base.sum_score
            , base.avg_score
            , @curRank := @curRank + 1 AS rank_ovr        
            , CASE  
                WHEN base.position_short = 'TW' THEN @curRank_TW := @curRank_TW+1 
                WHEN base.position_short = 'AW' THEN @curRank_AW := @curRank_AW+1 
                WHEN base.position_short = 'MF' THEN @curRank_MF := @curRank_MF+1 
                WHEN base.position_short = 'ST' THEN @curRank_ST := @curRank_ST+1 
                END as rank_pos
            , sysdate() as create_ts
        from (
            select  
                ftsy.player_id
                , pb.display_name
                , pb.position_short
                , round(avg(case when ftsy.appearance_stat = 1 then `".$stat."` else null end),1) as avg_score
                , sum(`".$stat."`) as sum_score         
            from ftsy_scoring_all_v ftsy
            inner join sm_playerbase pb
                on ftsy.player_id = pb.id
            inner join sm_fixtures_basic_v fix
                on fix.fixture_id = ftsy.fixture_id
                    and fix.season_id = '".$season_id."'
            group by    
                ftsy.player_id
                , ftsy.player_name
                , pb.position_short
            order by sum_score desc, avg_score desc
        ) base
         ,  (SELECT @curRank := 0) rnk
         ,  (SELECT @curRank_TW := 0) rnk_tw
         ,  (SELECT @curRank_AW := 0) rnk_aw
         ,  (SELECT @curRank_MF := 0) rnk_mf
         ,  (SELECT @curRank_ST := 0) rnk_st
    ");
}

mysqli_query($con, "DROP table IF EXISTS draft_player_ranking");

mysqli_query($con, " 

CREATE TABLE draft_player_ranking AS

SELECT  
    ftsy.player_id, ftsy.display_name, ftsy.position_short

    , ftsy.sum_score as sum_ftsy
    , ftsy.avg_score as avg_ftsy
    , ftsy.rank_ovr as rank_ovr_ftsy
    , ftsy.rank_pos as rank_pos_ftsy
    
    , ass.sum_score as sum_assists
    , ass.avg_score as avg_assists
    , ass.rank_ovr as rank_ovr_assists
    , ass.rank_pos as rank_pos_assists

    , blk.sum_score as sum_blocks
    , blk.avg_score as avg_blocks
    , blk.rank_ovr as rank_ovr_blocks
    , blk.rank_pos as rank_pos_blocks
    
    , cs.sum_score as sum_clean_sheet
    , cs.avg_score as avg_clean_sheet
    , cs.rank_ovr as rank_ovr_clean_sheet
    , cs.rank_pos as rank_pos_clean_sheet

    , clr.sum_score as sum_clearances
    , clr.avg_score as avg_clearances
    , clr.rank_ovr as rank_ovr_clearances
    , clr.rank_pos as rank_pos_clearances  
    
    , crs.sum_score as sum_crosses
    , crs.avg_score as avg_crosses
    , crs.rank_ovr as rank_ovr_crosses
    , crs.rank_pos as rank_pos_crosses  
    
    , drb.sum_score as sum_dribbles
    , drb.avg_score as avg_dribbles
    , drb.rank_ovr as rank_ovr_dribbles
    , drb.rank_pos as rank_pos_dribbles 

    , duel.sum_score as sum_duel_won
    , duel.avg_score as avg_duel_won
    , duel.rank_ovr as rank_ovr_duel_won
    , duel.rank_pos as rank_pos_duel_won 
    
    , goal.sum_score as sum_goal
    , goal.avg_score as avg_goal
    , goal.rank_ovr as rank_ovr_goal
    , goal.rank_pos as rank_pos_goal 
    
    , ints.sum_score as sum_ints
    , ints.avg_score as avg_ints
    , ints.rank_ovr as rank_ovr_ints
    , ints.rank_pos as rank_pos_ints 
    
    , minu.sum_score as sum_min_played
    , minu.rank_ovr as rank_ovr_min_played
    , minu.rank_pos as rank_pos_min_played
    
    , pass.sum_score as sum_passes
    , pass.avg_score as avg_passes
    , pass.rank_ovr as rank_ovr_passes
    , pass.rank_pos as rank_pos_passes
    
    , passkey.sum_score as sum_keypasses
    , passkey.avg_score as avg_keypasses
    , passkey.rank_ovr as rank_ovr_keypasses
    , passkey.rank_pos as rank_pos_keypasses
    
    , pen.sum_score as sum_penalties
    , pen.avg_score as avg_penalties
    , pen.rank_ovr as rank_ovr_penalties
    , pen.rank_pos as rank_pos_penalties
    
    , pens.sum_score as sum_penalties_saved
    , pens.avg_score as avg_penalties_saved
    , pens.rank_ovr as rank_ovr_penalties_saved
    , pens.rank_pos as rank_pos_penalties_saved
    
    , save.sum_score as sum_saves
    , save.avg_score as avg_saves
    , save.rank_ovr as rank_ovr_saves
    , save.rank_pos as rank_pos_saves
    
    , shot.sum_score as sum_shots
    , shot.avg_score as avg_shots
    , shot.rank_ovr as rank_ovr_shots
    , shot.rank_pos as rank_pos_shots
    
    , tack.sum_score as sum_tackles
    , tack.avg_score as avg_tackles
    , tack.rank_ovr as rank_ovr_tackles
    , tack.rank_pos as rank_pos_tackles

FROM rank_ftsy_score_2024 ftsy

INNER JOIN `rank_tackles_stat_2024` tack
    ON ftsy.player_id = tack.player_id

INNER JOIN `rank_shots_total_stat_2024` shot
    ON ftsy.player_id = shot.player_id

INNER JOIN `rank_saves_stat_2024` save
    ON ftsy.player_id = save.player_id

INNER JOIN `rank_pen_saved_stat_2024` pens
    ON ftsy.player_id = pens.player_id

INNER JOIN `rank_pen_scored_stat_2024` pen
    ON ftsy.player_id = pen.player_id
    
INNER JOIN `rank_key_passes_stat_2024` passkey
    ON ftsy.player_id = passkey.player_id

INNER JOIN `rank_passes_complete_stat_2024` pass
    ON ftsy.player_id = pass.player_id

INNER JOIN `rank_minutes_played_stat_2024` minu
    ON ftsy.player_id = minu.player_id

INNER JOIN `rank_interceptions_stat_2024` ints
    ON ftsy.player_id = ints.player_id

INNER JOIN `rank_goals_total_stat_2024` goal
    ON ftsy.player_id = goal.player_id

INNER JOIN `rank_dribbles_success_stat_2024` drb
    ON ftsy.player_id = drb.player_id

INNER JOIN `rank_duels_won_stat_2024` duel
    ON ftsy.player_id = duel.player_id

INNER JOIN `rank_crosses_total_stat_2024` crs
    ON ftsy.player_id = crs.player_id
    
INNER JOIN `rank_clearances_stat_2024` clr
    ON ftsy.player_id = clr.player_id

INNER JOIN `rank_clean_sheet_stat_2024` cs
    ON ftsy.player_id = cs.player_id

INNER JOIN `rank_assists_stat_2024` ass
    ON ftsy.player_id = ass.player_id
    
INNER JOIN `rank_blocks_stat_2024` blk
    ON ftsy.player_id = blk.player_id

");

?>