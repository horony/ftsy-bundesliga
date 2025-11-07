<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Set content type to JSON
header('Content-Type: application/json');

// Get current season from parameter table
$current_season_from_param = mysqli_query($con, "SELECT season_id FROM xa7580_db1.parameter")->fetch_object()->season_id;

// Get season from request, default to current season
$selected_season = isset($_GET['season']) ? intval($_GET['season']) : $current_season_from_param;

$response = [
    'success' => true,
    'season' => $selected_season,
    'current_season' => $current_season_from_param,
    'data' => []
];

try {
    // Function to get matches for a specific round
    function getRoundMatches($con, $season, $round, $includeTwoLegs = true) {
        $matches = [];
        
        // Sanitize inputs
        $season = intval($season);
        $round = mysqli_real_escape_string($con, $round);
        
        if ($includeTwoLegs && $round !== 'final') {
            // Two-legged matches (playoffs, quarters, semis)
            $sql = "
                SELECT  
                    hin.ftsy_match_id AS hin_match_id
                    , hin.ftsy_home_id AS home_id
                    , hin.ftsy_away_id AS away_id
                    , COALESCE(hin.ftsy_home_name, 'TBD') AS home_name
                    , COALESCE(hin.ftsy_away_name, 'TBD') AS away_name
                    , hin.ftsy_home_score AS hin_home_score
                    , hin.ftsy_away_score AS hin_away_score
                    , hin.buli_round_name AS hin_round
                    , rue.ftsy_match_id AS rue_match_id
                    , CASE 
                        WHEN hin.ftsy_home_id = rue.ftsy_home_id THEN rue.ftsy_home_score 
                        ELSE rue.ftsy_away_score 
                        END AS rue_home_score
                    , CASE 
                        WHEN hin.ftsy_away_id = rue.ftsy_away_id 
                        THEN rue.ftsy_away_score 
                        ELSE rue.ftsy_home_score 
                        END AS rue_away_score
                    , rue.buli_round_name AS rue_round
                    , ROUND(
                        COALESCE(hin.ftsy_home_score, 0) + 
                        COALESCE(CASE 
                            WHEN hin.ftsy_home_id = rue.ftsy_home_id 
                            THEN rue.ftsy_home_score 
                            ELSE rue.ftsy_away_score 
                        END, 0), 1
                    ) AS total_home_score
                    , ROUND(
                        COALESCE(hin.ftsy_away_score, 0) + 
                        COALESCE(CASE 
                            WHEN hin.ftsy_away_id = rue.ftsy_away_id 
                            THEN rue.ftsy_away_score 
                            ELSE rue.ftsy_home_score 
                        END, 0), 1
                    ) AS total_away_score
                FROM xa7580_db1.ftsy_schedule hin
                LEFT JOIN xa7580_db1.ftsy_schedule rue 
                    ON (rue.ftsy_home_id IN (hin.ftsy_home_id, hin.ftsy_away_id)
                    AND rue.match_type = 'cup'
                    AND rue.cup_round = '$round'
                    AND rue.cup_leg = 2
                    AND rue.ftsy_league_id = 1
                    AND rue.season_id = '$season'
                )
                WHERE 
                    hin.match_type = 'cup'
                    AND hin.cup_round = '$round'
                    AND hin.cup_leg = 1
                    AND hin.ftsy_league_id = 1
                    AND hin.season_id = '$season'
                ORDER BY hin.ftsy_match_id ASC
                LIMIT 4
            ";
        } else {
            // Single-leg match (final)
            $sql = "
                SELECT  
                    ftsy_match_id AS hin_match_id
                    , ftsy_home_id AS home_id
                    , ftsy_away_id AS away_id
                    , COALESCE(ftsy_home_name, 'TBD') AS home_name
                    , COALESCE(ftsy_away_name, 'TBD') AS away_name
                    , ftsy_home_score AS hin_home_score
                    , ftsy_away_score AS hin_away_score
                    , buli_round_name AS hin_round
                    , NULL AS rue_match_id
                    , NULL AS rue_home_score
                    , NULL AS rue_away_score
                    , NULL AS rue_round
                    , ROUND(COALESCE(ftsy_home_score, 0), 1) AS total_home_score
                    , ROUND(COALESCE(ftsy_away_score, 0), 1) AS total_away_score
                FROM xa7580_db1.ftsy_schedule
                WHERE 
                    match_type = 'cup'
                    AND cup_round = '$round'
                    AND cup_leg = 0
                    AND ftsy_league_id = 1
                    AND season_id = '$season'
                ORDER BY ftsy_match_id ASC
                LIMIT 4
            ";
        }
        
        $result = mysqli_query($con, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $match = [
                    'match_id' => $row['hin_match_id'],
                    'home_team' => [
                        'id' => intval($row['home_id']),
                        'name' => $row['home_name'],
                        'is_winner' => null
                    ],
                    'away_team' => [
                        'id' => intval($row['away_id']), 
                        'name' => $row['away_name'],
                        'is_winner' => null
                    ],
                    'scores' => [
                        'home_total' => $row['total_home_score'],
                        'away_total' => $row['total_away_score'],
                        'first_leg' => [
                            'home' => $row['hin_home_score'],
                            'away' => $row['hin_away_score'],
                            'round' => $row['hin_round'],
                            'match_id' => $row['hin_match_id']
                        ]
                    ],
                    'status' => 'scheduled'
                ];
                
                // Add second leg info if it exists
                if ($row['rue_match_id']) {
                    $match['scores']['second_leg'] = [
                        'home' => $row['rue_home_score'],
                        'away' => $row['rue_away_score'], 
                        'round' => $row['rue_round'],
                        'match_id' => $row['rue_match_id']
                    ];
                }
                
                // Determine winner and match status
                if ($row['total_home_score'] !== null && $row['total_away_score'] !== null) {
                    if ($row['total_home_score'] > $row['total_away_score']) {
                        $match['home_team']['is_winner'] = true;
                        $match['away_team']['is_winner'] = false;
                    } elseif ($row['total_away_score'] > $row['total_home_score']) {
                        $match['home_team']['is_winner'] = false;
                        $match['away_team']['is_winner'] = true;
                    }
                    $match['status'] = 'completed';
                } elseif ($row['hin_home_score'] !== null) {
                    $match['status'] = 'in_progress';
                }
                
                $matches[] = $match;
            }
        }
        return $matches;
    }
    
    // Get all tournament rounds
    $tournamentRounds = [
        'playoffs' => getRoundMatches($con, $selected_season, 'playoff', true),
        'quarters' => getRoundMatches($con, $selected_season, 'quarter', true),
        'semis' => getRoundMatches($con, $selected_season, 'semi', true),
        'final' => getRoundMatches($con, $selected_season, 'final', false)
    ];
    
    $response['data'] = $tournamentRounds;

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ];
}

// Return JSON response
echo json_encode($response);
?>