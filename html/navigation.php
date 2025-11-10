<?php
//include auth.php file on all secure pages
include("../php/auth.php");

// Define navigation links centrally
$base_url = "https://fantasy-bundesliga.de/html/";

// Get team name safely from session
if (isset($_SESSION["user_teamname"]) && !empty($_SESSION["user_teamname"])) {
    $team_name = strval(mb_convert_encoding($_SESSION["user_teamname"], 'UTF-8'));
} else {
    // Fallback: use username if teamname not available
    $team_name = isset($_SESSION["username"]) ? $_SESSION["username"] : 'unknown';
}

$nav_links = [
    'fantasy' => [
        'spieltag' => $base_url . 'spieltag.php',
        'tabelle' => $base_url . 'tabelle.php',
        'pokal' => $base_url . 'pokal.php',
        'stats' => $base_url . 'stats.php',
        'topxi' => $base_url . 'topxi.php',
        'draft' => $base_url . 'draft.php',
        'regelwerk' => $base_url . 'regelwerk.php'
    ],
    'transfermarkt' => [
        'aufnehmen' => $base_url . 'transfermarkt.php',
        'waiver' => $base_url . 'waiver.php',
        'trades' => $base_url . 'waiver_delete.php',
        'research' => $base_url . 'research.php?click_player=1018'
    ],
    'mein_team' => [
        'verwalten' => $base_url . 'mein_team.php?show_team=' . urlencode($team_name),
        'game_center' => 'https://fantasy-bundesliga.de/php/redirect-to-active-match.php'
    ],
    'bundesliga' => [
        'spieltag_buli' => $base_url . 'spieltag_buli.php'
    ]
];

$nav_labels = [
    'fantasy' => [
        'spieltag' => 'Spieltag',
        'tabelle' => 'Tabelle',
        'pokal' => 'Pokal',
        'stats' => 'Statistiken',
        'topxi' => 'Elf der Woche',
        'draft' => 'Draft',
        'regelwerk' => 'Regeln'
    ],
    'transfermarkt' => [
        'aufnehmen' => 'Spieler aufnehmen',
        'waiver' => 'Waiver Priorisierung',
        'trades' => 'Trades & Waivers',
        'research' => 'Spieler-Datenbank'
    ],
    'mein_team' => [
        'verwalten' => 'Team verwalten',
        'game_center' => 'Game-Center'
    ],
    'bundesliga' => [
        'spieltag_buli' => 'Spieltag'
    ]
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/nav.css">
</head>
<body>

<div class="main-nav">
    <!-- Hidden checkbox for mobile toggle -->
    <input type="checkbox" id="mobile-toggle" class="mobile-toggle">
    
    <div class="nav-container">
        <!-- Left side: Home + all navigation -->
        <div class="nav-left-group">
            <!-- Always visible: Home button -->
            <a href="https://fantasy-bundesliga.de/index.php" class="home-active">
                <i class="fas fa-home nav-item-icon"></i>Home
            </a>
            
            <!-- Desktop navigation - hidden on mobile -->
            <div class="desktop-nav">
                <div class="dropdown">
                    <button class="dropdownbtn"><i class="fas fa-gamepad nav-item-icon"></i>Fantasy</button>
                    <div class="dropdown-content">
                        <?php foreach($nav_links['fantasy'] as $key => $url): ?>
                            <a href="<?php echo $url; ?>"><?php echo $nav_labels['fantasy'][$key]; ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="dropdownbtn"><i class="fas fa-exchange-alt nav-item-icon"></i>Transfermarkt</button>
                    <div class="dropdown-content">
                        <?php foreach($nav_links['transfermarkt'] as $key => $url): ?>
                            <a href="<?php echo $url; ?>"><?php echo $nav_labels['transfermarkt'][$key]; ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="dropdownbtn"><i class="fas fa-heart nav-item-icon"></i>Mein Team</button>
                    <div class="dropdown-content">
                        <?php foreach($nav_links['mein_team'] as $key => $url): ?>
                            <a href="<?php echo $url; ?>"><?php echo $nav_labels['mein_team'][$key]; ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="dropdownbtn"><i class="fas fa-futbol nav-item-icon"></i>Bundesliga</button>
                    <div class="dropdown-content">
                        <?php foreach($nav_links['bundesliga'] as $key => $url): ?>
                            <a href="<?php echo $url; ?>"><?php echo $nav_labels['bundesliga'][$key]; ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right side: Mein Account (desktop) + Burger Menu (mobile) -->
        <div class="nav-right-group">
            <!-- Desktop account -->
            <div class="desktop-account">
                <a href="https://fantasy-bundesliga.de/html/account_verwaltung.php"><i class="fas fa-user-cog nav-item-icon"></i>Mein Account</a>
            </div>
            
            <!-- Mobile burger menu -->
            <label for="mobile-toggle" class="icon">&#9776;</label>
        </div>
        
        <!-- Mobile navigation menu - hidden by default -->
        <div class="mobile-nav">
            <div class="dropdown">
                <input type="radio" name="mobile-dropdown" id="mobile-fantasy-toggle" class="dropdown-toggle">
                <label for="mobile-fantasy-toggle" class="dropdownbtn"><i class="fas fa-gamepad nav-item-icon"></i>Fantasy</label>
                <div class="dropdown-content">
                    <?php foreach($nav_links['fantasy'] as $key => $url): ?>
                        <a href="<?php echo $url; ?>"><?php echo $nav_labels['fantasy'][$key]; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="dropdown">
                <input type="radio" name="mobile-dropdown" id="mobile-transfermarkt-toggle" class="dropdown-toggle">
                <label for="mobile-transfermarkt-toggle" class="dropdownbtn"><i class="fas fa-exchange-alt nav-item-icon"></i>Transfermarkt</label>
                <div class="dropdown-content">
                    <?php foreach($nav_links['transfermarkt'] as $key => $url): ?>
                        <a href="<?php echo $url; ?>"><?php echo $nav_labels['transfermarkt'][$key]; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="dropdown">
                <input type="radio" name="mobile-dropdown" id="mobile-mein-team-toggle" class="dropdown-toggle">
                <label for="mobile-mein-team-toggle" class="dropdownbtn"><i class="fas fa-heart nav-item-icon"></i>Mein Team</label>
                <div class="dropdown-content">
                    <?php foreach($nav_links['mein_team'] as $key => $url): ?>
                        <a href="<?php echo $url; ?>"><?php echo $nav_labels['mein_team'][$key]; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="dropdown">
                <input type="radio" name="mobile-dropdown" id="mobile-bundesliga-toggle" class="dropdown-toggle">
                <label for="mobile-bundesliga-toggle" class="dropdownbtn"><i class="fas fa-futbol nav-item-icon"></i>Bundesliga</label>
                <div class="dropdown-content">
                    <?php foreach($nav_links['bundesliga'] as $key => $url): ?>
                        <a href="<?php echo $url; ?>"><?php echo $nav_labels['bundesliga'][$key]; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Hidden radio button to close all dropdowns -->
            <input type="radio" name="mobile-dropdown" id="mobile-close-all" class="dropdown-toggle" style="display: none;">
            
            <a href="https://fantasy-bundesliga.de/html/account_verwaltung.php"><i class="fas fa-user-cog nav-item-icon"></i>Mein Account</a>
        </div>
    </div>
</div>

<script>
// Allow closing mobile dropdowns by clicking on already open menu
document.addEventListener('DOMContentLoaded', function() {
    const mobileNav = document.querySelector('.mobile-nav');
    const labels = mobileNav.querySelectorAll('.dropdownbtn');
    
    labels.forEach(label => {
        label.addEventListener('click', function(e) {
            const radio = document.getElementById(this.getAttribute('for'));
            
            // If this radio is already checked, uncheck it by selecting the hidden close radio
            if (radio && radio.checked) {
                e.preventDefault();
                document.getElementById('mobile-close-all').checked = true;
            }
        });
    });
    
    // Function to update dropdown position
    function updateDropdownPosition() {
        const nav = document.querySelector('.main-nav');
        const dropdowns = document.querySelectorAll('.main-nav .dropdown-content');
        
        if (nav && dropdowns.length > 0) {
            const navRect = nav.getBoundingClientRect();
            const topPosition = navRect.bottom;
            
            dropdowns.forEach(dropdown => {
                dropdown.style.top = topPosition + 'px';
            });
        }
    }
    
    // Update position on scroll and resize
    window.addEventListener('scroll', updateDropdownPosition);
    window.addEventListener('resize', updateDropdownPosition);
    
    // Update position when dropdown is hovered
    const dropdownTriggers = document.querySelectorAll('.main-nav .dropdown');
    dropdownTriggers.forEach(trigger => {
        trigger.addEventListener('mouseenter', updateDropdownPosition);
    });
    
    // Initial position update
    updateDropdownPosition();
});
</script>

</body>
</html>