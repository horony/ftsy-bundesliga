SELECT 
    base.id
    , team.logo_path as verein_logo
    , base.display_name as name
    , base.position_short as pos
    , case 
        when base.is_suspended is not null then 'suspended'
        when base.injured = 1 then 'injured'
        else 'fit'
        end as fitness
    , snap.ftsy_score_sum as total_fb_score
    , snap.ftsy_score_avg_last_3 as last3_avg_fb_score
    , snap.ftsy_score_last as last1_total_fb_score
    , snap.ftsy_score_avg as avg_fb_score
    , own.1_ftsy_owner_type as Besitzer
    , opp.opp_code
    , opp.opp_name
    , pts_allowed.rank as rank_allowed
    , pts_allowed.avg_allowed
    , proj.ftsy_score_projected
FROM xa7580_db1.sm_playerbase base
INNER JOIN xa7580_db1.ftsy_player_ownership own
    ON own.player_id = base.id 
INNER JOIN xa7580_db1.sm_teams team
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
WHERE  
    ( own.1_ftsy_owner_id IS NULL OR own.1_ftsy_owner_type != ? )
    AND base.rostered = 1
ORDER BY total_fb_score DESC, avg_fb_score DESC, last3_avg_fb_score DESC, last1_total_fb_score DESC