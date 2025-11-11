<?php include("../php/auth.php"); ?>
<html>
<head>
    <title>FANTASY BUNDESLIGA</title> 
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=3, minimum-scale=0.1, user-scalable=no, minimal-ui">
  
    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/topxi.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/overall.css">

    <!-- External libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>

    <!-- Custom scripts -->
    <script type="text/javascript" src="../js/topxi-clickable-elements.js"></script>
    <script type="text/javascript" src="../js/topxi-display-sub-nav.js"></script>	
    <script type="text/javascript" src="../js/topxi-display-formation.js"></script>

    <script>
        /* Default settings when opening page */
        $(document).ready(function default_load(){
            /* Reset all values */
            $choice = 'FANTASY-BUNDESLIGA';
            $(".default").css("color", "#4caf50");
                $('.lvl1_nav').css("display", "none");
                $('.lvl2_nav').css("display", "none");   

            // Check for direct navigation parameters
            const urlParams = new URLSearchParams(window.location.search);
            const nav = urlParams.get('nav');
            const season = urlParams.get('season');
            const round = urlParams.get('round');
            
            if (nav === 'FABU' && season && round) {
                console.log('Direct navigation to round:', round, 'season:', season);
                
                // Remove default highlighting from All-Time button
                $(".default").css("color", "");
                
                // Make navigation menus visible
                $('.lvl1_nav').css("display", "block");
                $('.lvl2_nav').css("display", "block");
                
                // Step 1: Load sub nav 1 (seasons) without highlighting the main button
                show_sub_nav_1($choice);
                
                // Step 2: Load sub nav 2 (rounds) for the specific season
                setTimeout(function() {
                    console.log('Loading sub nav 2 for season:', season);
                    show_sub_nav_2('FANTASY-BUNDESLIGA', season);
                }, 200);
                
                // Step 3: Wait for sub nav 2 to load, then highlight ONLY the specific season
                setTimeout(function() {
                    console.log('Highlighting season:', season);
                    // First remove any existing highlights
                    $('.lvl1_button').css('color', '');
                    
                    $('.lvl1_button').each(function() {
                        var onclick = $(this).attr('onclick') || '';
                        console.log('Checking season button onclick:', onclick);
                        // Look for the specific season ID in the onclick attribute
                        if (onclick.includes('"' + season + '"') && onclick.includes('SZN')) {
                            $(this).css('color', '#4caf50');
                            console.log('Highlighted season button');
                        }
                    });
                }, 600);
                
                // Step 4: Load the specific round data and highlight ONLY the specific round
                setTimeout(function() {
                    console.log('Loading topxi for round:', round);
                    show_topxi('FABU','RND', season, round);
                    
                    // Highlight the correct round
                    setTimeout(function() {
                        console.log('Highlighting round:', round);
                        // First remove any existing highlights
                        $('.lvl2_button').css('color', '');
                        
                        $('.lvl2_button').each(function() {
                            var text = $(this).text().trim();
                            console.log('Checking round button text:', text);
                            if (text === round.toString()) {
                                $(this).css('color', '#4caf50');
                                console.log('Highlighted round button');
                            }
                        });
                    }, 200);
                }, 1000);
                
            } else {
                /* Load all-time topix */
                show_topxi('FABU','OVR','','');
                show_sub_nav_1($choice);
                show_sub_nav_2();
            }
        });
    </script>
</head>

<body>

    <!-- Header image -->
    <header>
        <?php require "header.php"; ?>
    </header>	

    <!-- Website navigation -->
    <div id = "hilfscontainer">
        <?php include("navigation.php"); ?>
    </div>

    <!-- Content -->
    <div id = "wrapper">
        <div id="content_wrapper">
                <!-- Top XI specific navigation -->
                <div id="view_nav">
                    <ul>
                        <li><a class="button default" onclick="clickable(this); show_sub_nav_1(this); show_sub_nav_2(); show_topxi('FABU','OVR','0','0');">FANTASY-BUNDESLIGA</a></li>
                        <li><a class="button" onclick="clickable(this); show_sub_nav_1(this); show_sub_nav_2('FANTASY-TEAMS','-1'); show_topxi('USER','OVR','0','0');">FANTASY-TEAMS</a></li>	
                        <li><a class="button" onclick="clickable(this); show_sub_nav_1(this); show_sub_nav_2('BUNDESLIGA-TEAMS','-1'); show_topxi('BULI','OVR','0','0');">BUNDESLIGA-TEAMS</a></li>
                    </ul>
                </div>
                <div id="sub_nav_wrapper">
                    <div id="sub_nav_1" class="sub_nav"></div>
                    <div id="sub_nav_2" class="sub_nav"></div>
                </div>

                <!-- Functions load data here -->
                <div id="content">
                </div>
            </div>
        </div>
    </div> 
</body>
</html>