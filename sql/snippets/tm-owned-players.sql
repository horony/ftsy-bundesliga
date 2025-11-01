SELECT 
    base.id
    , logo_path AS verein_logo
    , base.display_name AS name
    , base.position_short AS pos
    , CASE 
        WHEN base.is_suspended IS NOT NULL THEN 'suspended'
        WHEN base.injured = 1 THEN 'injured'
        ELSE 'fit'
        END AS fitness
    , snap.ftsy_score_sum AS total_fb_score
    , snap.ftsy_score_avg_last_3 AS last3_avg_fb_score
    , snap.ftsy_score_last AS last1_total_fb_score
    , snap.ftsy_score_avg AS avg_fb_score
    , own.1_ftsy_owner_type AS Besitzer
    , opp.opp_code
    , opp.opp_name
    , pts_allowed.rank AS rank_allowed
    , pts_allowed.avg_allowed
    , proj.ftsy_score_projected
FROM xa7580_db1.sm_playerbase base
INNER JOIN xa7580_db1.ftsy_player_ownership own
    ON own.player_id = base.id 
LEFT JOIN xa7580_db1.sm_teams team
    ON team.id = base.current_team_id
LEFT JOIN xa7580_db1.ftsy_scoring_snap snap
     ON snap.id = base.id
LEFT JOIN xa7580_db1.sm_fixture_per_team_akt_v opp
    ON opp.team_id = base.current_team_id 
LEFT JOIN xa7580_db1.ftsy_points_allowed pts_allowed
    ON pts_allowed.position_short = base.position_short
    AND pts_allowed.opp_team_id = opp.opp_id
LEFT JOIN xa7580_db1.ftsy_scoring_projection_v proj
    ON proj.player_id = base.id         
WHERE own.1_ftsy_owner_id = ?
ORDER BY total_fb_score DESC, avg_fb_score DESC, last3_avg_fb_score DESC, last1_total_fb_score DESC