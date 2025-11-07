<?php include("../php/auth.php"); ?>
<!DOCTYPE html>
<html>
<head>
    <title>FANTASY BUNDESLIGA - POKAL</title>
    <meta name="robots" content="noindex">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/pokal.css">
    
    <!-- Custom scripts -->
    <script type="text/javascript" src="../js/cup-display-bracket.js"></script>
</head>

<body onload="loadBracket()">
    <!-- Header image -->
    <header>
        <?php require "header.php"; ?>
    </header>

    <!-- Navigation -->
    <div id="hilfscontainer">
        <?php include("navigation.php"); ?>
    </div>

    <!-- Main Content -->
    <main>
        <div class="tournament-wrapper">
            <!-- Tournament Header -->
            <div class="tournament-header">
                <h1 id="headline">FANTASY BUNDESLIGA POKAL 🏆</h1>
                <div class="tournament-round-selector">
                    <select id="season_select" class="season-select" onchange="changeSeason()">
                        <?php
                        include("../secrets/mysql_db_connection.php");
                        
                        // Get available seasons
                        $seasons_query = mysqli_query($con, "
                            SELECT DISTINCT 
								season_id
								, season_name AS season_display
                            FROM xa7580_db1.sm_seasons 
                            WHERE 
								season_id >= 17361
                            ORDER BY season_id DESC
                        ");
                        
                        // Get current season from parameter
                        $current_season = mysqli_query($con, "SELECT season_id FROM xa7580_db1.parameter")->fetch_object()->season_id;
                        
                        while($season = mysqli_fetch_array($seasons_query)) {
                            $selected = ($season['season_id'] == $current_season) ? 'selected' : '';
                            echo "<option value='".$season['season_id']."' $selected>".$season['season_display']."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Tournament Bracket -->
            <div id="tournament_bracket">
                <div class="bracket_container">
                    
                    <!-- Playoffs/Qualifiers -->
                    <div class="bracket_round" id="round_playoffs">
                        <div class="round_header">PLAYOFFS</div>
                        <div class="round_subtitle">Bottom 4 vs Each Other</div>
                        <div class="matches_container" id="playoff_matches">
                            <!-- Playoff matches will be loaded here -->
                        </div>
                    </div>

                    <!-- Quarter Finals -->
                    <div class="bracket_round" id="round_quarters">
                        <div class="round_header">VIERTELFINALE</div>
                        <div class="round_subtitle">Top 6 + 2 Playoff Winners</div>
                        <div class="matches_container" id="quarter_matches">
                            <!-- Quarter final matches will be loaded here -->
                        </div>
                    </div>

                    <!-- Semi Finals -->
                    <div class="bracket_round" id="round_semis">
                        <div class="round_header">HALBFINALE</div>
                        <div class="round_subtitle">4 Quarter Winners</div>
                        <div class="matches_container" id="semi_matches">
                            <!-- Semi final matches will be loaded here -->
                        </div>
                    </div>

                    <!-- Final -->
                    <div class="bracket_round" id="round_final">
                        <div class="round_header">FINALE</div>
                        <div class="round_subtitle">2 Semi Winners</div>
                        <div class="matches_container" id="final_matches">
                            <!-- Final match will be loaded here -->
                        </div>
                    </div>

                </div>
            </div>

            <!-- Loading indicator -->
            <div id="loading_indicator" style="display: none;">
                <div class="loading_spinner"></div>
                <div>Thinking...</div>
            </div>

        </div>
    </main>
</body>
</html>