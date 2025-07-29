DROP VIEW IF EXISTS dev_ftsy_scoring_all_aggregated_v
;

CREATE VIEW dev_ftsy_scoring_all_aggregated_v AS 
SELECT 
    a.player_id
    , a.player_name
    , base.position_short
    , a.sum_score
    , a.avg_score
    , a.anz_spiele
    , b.ftsy_score_sum as old_sum_score
    , b.ftsy_score_avg as old_savg_score
    , b.appearance_stat_sum as old_anz_spiele
    , b.ftsy_score_rank_all as old_rank_overall
    , b.ftsy_score_rank_pos as old_rank_pos
    , a.sum_score - b.ftsy_score_sum as diff_ftsy_score_sum
    , a.avg_score - b.ftsy_score_avg as diff_ftsy_score_avg      
FROM ftsy_scoring_all_aggregated_v a
LEFT JOIN sm_playerbase base
    ON base.id = a.player_id
LEFT JOIN ftsy_scoring_snap b
    ON a.player_id = b.id
ORDER BY a.sum_score desc
;