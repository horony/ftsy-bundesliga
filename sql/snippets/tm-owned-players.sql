SELECT 
    base.id
    , base.logo_path AS verein_logo
    , base.display_name AS name
    , base.position_short AS pos
    , base.player_status
    , base.player_status_logo_path
    , base.sidelined_reason
    , snap.ftsy_score_sum AS total_fb_score
    , snap.ftsy_score_avg_last_3 AS last3_avg_fb_score
    , snap.ftsy_score_last AS last1_total_fb_score
    , snap.ftsy_score_avg AS avg_fb_score
    , base.1_ftsy_owner_type AS Besitzer
    , opp.opp_code
    , opp.opp_name
    , pts_allowed.rank AS rank_allowed
    , pts_allowed.avg_allowed
    , proj.ftsy_score_projected
FROM xa7580_db1.sm_playerbase_basic_v base
LEFT JOIN xa7580_db1.ftsy_scoring_snap snap
    ON snap.id = base.id
LEFT JOIN xa7580_db1.sm_fixture_per_team_akt_v opp
    ON opp.team_id = base.team_id 
LEFT JOIN xa7580_db1.ftsy_points_allowed pts_allowed
    ON pts_allowed.position_short = base.position_short
    AND pts_allowed.opp_team_id = opp.opp_id
LEFT JOIN xa7580_db1.ftsy_scoring_projection_v proj
    ON proj.player_id = base.id 
WHERE base.1_ftsy_owner_id = ?
ORDER BY total_fb_score DESC, avg_fb_score DESC, last3_avg_fb_score DESC, last1_total_fb_score DESC