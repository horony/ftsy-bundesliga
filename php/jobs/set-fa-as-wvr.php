<?php
    // scheduled job that changes a player status from free agent (FA) to waiver (WVR) during a active round

    echo nl2br("Running job set-fa-as-wvr.php \n");
    echo nl2br("Connecting to MySQL DB \n");

    include('../../secrets/mysql_db_connection.php');

    echo nl2br("Executing update on ftsy_player_ownership.1_ftsy_owner_type \n");

    // Change players with kickoff in the past from FA to WVR for ftsy league 1
    mysqli_query($con, "
        UPDATE xa7580_db1.ftsy_player_ownership owr
        INNER JOIN xa7580_db1.sm_playerbase base
            ON base.id = owr.player_id
        LEFT JOIN xa7580_db1.sm_fixtures fix
            ON (base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id)
            AND	fix.round_name = (SELECT spieltag from xa7580_db1.parameter)
            AND	fix.season_id = (SELECT season_id from xa7580_db1.parameter)
        SET owr.1_ftsy_owner_type = 'WVR'
        WHERE
            owr.1_ftsy_owner_type = 'FA' 
            AND fix.kickoff_ts <= now()
    ");

    echo nl2br("Exiting script \n");
?>