<?php include("../php/auth.php"); ?>
<!DOCTYPE html>
<html>

<head>
    <title>FANTASY BUNDESLIGA</title> 
    <meta name="robots" content="noindex">
    <meta charset="UTF-8">   
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/spieltag.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Custom scripts -->
    <script type="text/javascript" src="../js/spieltag-swipe-fixtures.js"></script>
    <script type="text/javascript" src="../js/spieltag-display-fixtures.js"></script>
    <script type="text/javascript" src="../js/spieltag-clickable-fixtures.js"></script>
</head>

<body>

<!-- Header image -->
<header>
    <?php require "header.php"; ?>
</header>
    
<?php include("navigation.php"); ?>

<main>
    <div id="hilfscontainer">
        <div class="flex-grid-fantasy"> 
            <div class="col">

                <!-- Fixture table is loaded here through spieltag-display-fixtures.js -->
                <div id="spieltag_tabelle" style="overflow-y: auto;">
                </div>
                <!-- Button for choosing a round -->
                <div class="spieltag-select-container">
                    <label for="spieltag-select" class="spieltag-label">Spieltag wählen:</label>
                    <select id="spieltag-select" class="spieltag-select" onfocus='this.size=10;' onblur='this.size=1;' onchange='this.size=1; this.blur();'>
                        <?php for ($i = 1; $i <= 34; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?>. Spieltag</option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>  
    </div>
</main>
</body>
</html>