<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Collect meta data
$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

$result_params_sql = mysqli_query($con, "SELECT spieltag, season_id from xa7580_db1.parameter ") -> fetch_object();
$akt_spieltag = $result_params_sql->spieltag;
$akt_season_id = $result_params_sql->season_id;

$selected_spieltag = $_GET["spieltag"];

// Display headline
echo "<div id='headline'><h2>Bundesliga Spieltag " . $selected_spieltag . "</h2></div>";

$cte_sql_ftsy_score = '';

if($selected_spieltag < $akt_spieltag) {

    // Top XI Link for past rounds
    echo "<div id='topxi-link-container' class='topxi-link-container'>";
        echo "<a href='topxi.php?nav=FABU&season=" . $akt_season_id . "&round=" . $selected_spieltag . "' id='topxi-link' class='topxi-link' title='Zur Elf der Woche'>» Elf der Woche ⭐</a>";
    echo "</div>";

    $cte_sql_ftsy_score = "
        SELECT 
            current_team_id AS team_id
            , SUM(ftsy_score) AS team_ftsy_score_sum
        FROM xa7580_db1.ftsy_scoring_hist
        WHERE 
            round_name = '".$selected_spieltag."'
            AND season_id = '".$akt_season_id."'
        GROUP BY current_team_id
    ";
} elseif ($selected_spieltag == $akt_spieltag) {
    $cte_sql_ftsy_score = "
        SELECT 
            pb.current_team_id AS team_id
            , ROUND(COALESCE(SUM(akt.ftsy_score),0),1) AS team_ftsy_score_sum
        FROM xa7580_db1.sm_playerbase pb
        LEFT JOIN xa7580_db1.ftsy_scoring_akt_mv akt
            ON akt.player_id = pb.id
        WHERE 
            pb.current_team_id IS NOT NULL
        GROUP BY pb.current_team_id
    ";
}

// Fetch fixtures with fantasy scores
$result = mysqli_query($con,"
    WITH cte_team_ftsy_score AS (
        ".$cte_sql_ftsy_score."
        )
    SELECT 
        v.fixture_id
        , v.localteam_id
        , v.visitorteam_id
        , v.localteam_name
        , v.visitorteam_name
        , t_local.logo_path AS localteam_logo_path
        , t_away.logo_path AS visitorteam_logo_path
        , COALESCE(v.localteam_score, 0) AS localteam_score
        , COALESCE(v.visitorteam_score, 0) AS visitorteam_score
        , COALESCE(cte_home.team_ftsy_score_sum, 0) AS home_team_ftsy_score
        , COALESCE(cte_away.team_ftsy_score_sum, 0) AS away_team_ftsy_score
        , v.fixture_status
        , CONCAT(
            CASE DAYOFWEEK(v.kickoff_ts)
                WHEN 1 THEN 'So.'
                WHEN 2 THEN 'Mo.'
                WHEN 3 THEN 'Di.'
                WHEN 4 THEN 'Mi.'
                WHEN 5 THEN 'Do.'
                WHEN 6 THEN 'Fr.'
                WHEN 7 THEN 'Sa.'
            END,
            ' ',
            DATE_FORMAT(v.kickoff_ts, '%d.%m.%Y %H:%i')
            ) AS kickoff_ts
    FROM xa7580_db1.sm_fixtures_basic_v v
    LEFT JOIN xa7580_db1.sm_teams t_local
        ON v.localteam_id = t_local.id
    LEFT JOIN xa7580_db1.sm_teams t_away
        ON v.visitorteam_id = t_away.id
    INNER JOIN cte_team_ftsy_score cte_home
        ON cte_home.team_id = v.localteam_id
    INNER JOIN cte_team_ftsy_score cte_away
        ON cte_away.team_id = v.visitorteam_id
    WHERE
        v.round_name = '".$selected_spieltag."'
        AND v.season_id = '".$akt_season_id."'
    ORDER BY v.kickoff_ts ASC
    ;");


// Display fixtures in HTML
while($col = mysqli_fetch_array($result)){
    echo "<div class='league-matchup-row-item'>";
        echo "<div class='matchup-header'>";
            echo "<div class='matchup-row' style='cursor: pointer;' onclick='viewBuliFixture(".strval($col['fixture_id']).");'>";
                // Home Team
                echo "<div class='user'>";
                    echo "<div class='matchup-owner-item'>";
                        echo "<div class='owner-container'>";
                            echo "<div class='row space-between'>";
                                echo "<div class='row'>";
                                    echo "<div>";
                                        // Team Logo
                                        echo "<div style='background-image: url(" . $col['localteam_logo_path']. "); width: 40px; height: 40px; flex: 0 0 40px; background-size: contain; position: relative;'></div>";
                                    echo "</div>";
                                    echo "<div class='meta'>";
                                        // Teamname
                                        echo "<div class='teamname'>";
                                            echo "<span class='team-full-name'>".htmlspecialchars($col['localteam_name'])."</span>";
                                            echo "<span class='team-short-code'>".htmlspecialchars($col['localteam_name'])."</span>";
                                        echo "</div>";
                                    echo "</div>";
                                echo "</div>";
                                // Score
                                echo "<div class='roster-score-and-projection-matchup'>";
                                    echo "<div class='scoreboard-buli'>".$col['localteam_score']."</div>";
                                echo "</div>";
                            echo "</div>";
                        echo "</div>";
                        // Bottom row with descriptions and projections
                        echo "<div class='bottom-row space-between'>";
                            echo "<div class='row'>";
                                echo "<div class='description-one'>";
                                    echo $col['kickoff_ts'];
                                echo "</div>";
                                echo "<div class='description-two'>";
                                    echo $col['fixture_status'];
                                echo "</div>";
                            echo "</div>";
                            echo "<div class='roster-score-and-projection-matchup'>";
                                echo "<div class='projections'>".$col['home_team_ftsy_score']."</div>";
                            echo "</div>";
                        echo "</div>";
                    echo "</div>";
                echo "</div>";
                // VS-Label
                echo "<div class='vs-label'><div class='label'>VS</div></div>";
                // Away Team
                echo "<div class='user reverse'>";
                    echo "<div class='matchup-owner-item'>";
                        echo "<div class='owner-container'>";
                            echo "<div class='row flip space-between'>";
                                echo "<div class='row flip'>";
                                    echo "<div>";
                                        // Team Logo
                                        echo "<div style='background-image: url(" . $col['visitorteam_logo_path']. "); width: 40px; height: 40px; flex: 0 0 40px; background-size: contain; position: relative;'></div>";
                                    echo "</div>";
                                    echo "<div class='meta flip'>";
                                        // Teamname
                                        echo "<div class='teamname'>";
                                            echo "<span class='team-full-name'>".htmlspecialchars($col['visitorteam_name'])."</span>";
                                            echo "<span class='team-short-code'>".htmlspecialchars($col['visitorteam_name'])."</span>";
                                        echo "</div>";
                                    echo "</div>";
                                echo "</div>";
                                echo "<div class='roster-score-and-projection-matchup flip'>";
                                    // Score
                                    echo "<div class='scoreboard-buli'>".$col['visitorteam_score']."</div>";
                                echo "</div>";
                            echo "</div>";
                        echo "</div>";
                        // Bottom row with descriptions and projections
                        echo "<div class='bottom-row flip space-between'>";
                            echo "<div class='row flip'>";
                                echo "<div class='description-one'>";
                                    echo $col['kickoff_ts'];
                                echo "</div>";
                                echo "<div class='description-two'>";
                                    echo $col['fixture_status'];
                                echo "</div>";
                            echo "</div>";
                            echo "<div class='roster-score-and-projection-matchup flip'>";
                                echo "<div class='projections'>".$col['away_team_ftsy_score']."</div>";
                            echo "</div>";
                        echo "</div>";
                    echo "</div>";
                echo "</div>";
            echo "</div>";
        echo "</div>";
    echo "</div>";
}

mysqli_close($con);
?>