<?php include("../php/auth.php"); ?>
<?php include("../secrets/mysql_db_connection.php"); ?>
<!DOCTYPE html>
<html>
<head>
    <title>FANTASY BUNDESLIGA</title>

    <!-- Meta Tags -->
    <meta name="robots" content="noindex">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" href="../css/overall.css">
    <link rel="stylesheet" type="text/css" href="../css/spieltag.css">
    <link rel="stylesheet" type="text/css" href="../css/nav.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- External Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    
    <!-- Custom scripts -->
    <script type="text/javascript" src="../js/spieltag-display-fixtures.js"></script>
    <script type="text/javascript" src="../js/spieltag-clickable-fixtures.js"></script>
    <script type="text/javascript" src="../js/spieltag-change-round.js"></script>
</head>
<body>
<!-- Header --> 
<header>
    <?php require "header.php"; ?>
</header>
<!-- Navigation -->
<?php include("navigation.php"); ?>
<?php
// get current spieltag and season
$parameter_data = mysqli_query($con, "SELECT spieltag, season_id FROM xa7580_db1.parameter") -> fetch_object();
$akt_spieltag = $parameter_data -> spieltag;
$akt_season_id = $parameter_data -> season_id;

// fetch match_type per round
$round_types = [];
$res_rt = mysqli_query($con, "
    SELECT 
        sch.buli_round_name AS rname
        , MIN(sch.match_type) AS match_type
    FROM xa7580_db1.ftsy_schedule sch
    WHERE 
        sch.season_id = '$akt_season_id'
    GROUP BY sch.buli_round_name
");
while ($r = mysqli_fetch_assoc($res_rt)) {
    $n = intval($r['rname']);
    $round_types[$n] = $r['match_type'];
}

// optionally allow a preselected round (from GET)
$preselect = $akt_spieltag;
?>

<main>
    <div class="league-matchups">
        <div id="spieltag_tabelle" class="matchups-table" style="overflow-y: auto;">
            <!-- Fixture table is loaded here through spieltag-display-fixtures.js -->
        </div>
        <div class="matchups-header">
            <div class="matchups-round-selector">
                <!-- Visible custom picker (grid) -->
                <div class="spieltag-picker" id="spieltag-picker" aria-haspopup="true">
                    <button type="button" class="spieltag-trigger" id="spieltag-trigger" aria-expanded="false">
                        Spieltag <span id="spieltag-current"><?= htmlspecialchars($preselect) ?></span> ▾
                    </button>
                    <div class="spieltag-grid" id="spieltag-grid" aria-hidden="true" role="menu">
                        <?php for ($i = 1; $i <= 34; $i++):
                            $type = isset($round_types[$i]) ? $round_types[$i] : 'league';
                            $classes = [];
                            if ($i < $akt_spieltag) $classes[] = 'finished';
                            elseif ($i == $akt_spieltag) $classes[] = 'current';
                            else $classes[] = 'upcoming';
                            if ($type === 'cup') $classes[] = 'cup';
                            if ($i == $preselect) $classes[] = 'selected';
                            $cls = implode(' ', $classes);
                        ?>
                            <button type="button" class="spieltag-cell <?= $cls ?>" data-value="<?= $i ?>">
                                <span class="cup-back"><?= ($type === 'cup') ? '🏆' : '' ?></span>
                                <span class="cell-number"><?= $i ?></span>
                            </button>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Hidden select kept for compatibility with existing JS -->
                <select id="spieltag-select" class="spieltag-select" style="display:none;">
                    <?php for ($i = 1; $i <= 34; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $preselect) ? 'selected' : '' ?>><?= $i ?>. Spieltag</option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
    </div>
    <!-- Footnote -->
    <div class='matchday-footnote'>
        Legende: 🍀 = Lucky Looser | NS = Not Started, FT = Full Time, LIVE = Spiel läuft
    </div>
</main>
</body>
</html>