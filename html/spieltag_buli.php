<?php include("../php/auth.php"); ?>
<?php include("../secrets/mysql_db_connection.php"); ?>
<?php
// get current spieltag and season
$result_params_sql = mysqli_query($con, "SELECT spieltag, season_id from xa7580_db1.parameter ") -> fetch_object();
$akt_spieltag = $result_params_sql->spieltag;
$akt_season_id = $result_params_sql->season_id;

// optionally allow a preselected round (from GET)
$preselect = isset($_GET['round']) && is_numeric($_GET['round']) ? (int)$_GET['round'] : $akt_spieltag;
?>
<!DOCTYPE html>
<html>
<head>
    <title>FANTASY BUNDESLIGA</title>
    <meta name="robots" content="noindex">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/spieltag.css">
    <link rel="stylesheet" type="text/css" href="../css/nav.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Custom scripts -->
    <script type="text/javascript" src="../js/spieltag-buli-display-fixtures.js"></script>
    <script type="text/javascript" src="../js/spieltag-buli-clickable-fixtures.js"></script>
    <script type="text/javascript" src="../js/spieltag-change-round.js"></script>
    <script>
        // Pass current season and spieltag data to JavaScript
        window.currentSpieltag = <?= $akt_spieltag ?>;
        window.currentSeasonId = <?= $akt_season_id ?>;
    </script>
</head>
<body>
<!-- Header --> 
<header>
    <?php require "header.php"; ?>
</header>
<!-- Navigation -->
<?php include("navigation.php"); ?>

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
                        <?php for ($i = 1; $i <= $akt_spieltag; $i++):
                            $classes = [];
                            if ($i < $akt_spieltag) $classes[] = 'finished';
                            elseif ($i == $akt_spieltag) $classes[] = 'current';
                            else $classes[] = 'upcoming';
                            if ($i == $preselect) $classes[] = 'selected';
                            $cls = implode(' ', $classes);
                        ?>
                        <button type="button" class="spieltag-cell <?= $cls ?>" data-value="<?= $i ?>">
                            <span class="cell-number"><?= $i ?></span>
                        </button>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Hidden select kept for compatibility with existing JS -->
                <select id="spieltag-select" class="spieltag-select" style="display:none;">
                    <?php for ($i = 1; $i <= $akt_spieltag; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == $preselect) ? 'selected' : '' ?>><?= $i ?>. Spieltag</option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
    </div>
    <!-- Footnote -->
    <div class='matchday-footnote'>
       Legende: NS = Not Started, 1st = 1st Half,  2nd = 2nd Half, HT = Halftime, FT = Fulltime, P = Postponed
    </div>
</main>
</body>
</html>