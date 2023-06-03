<?php
//include auth.php file on all secure pages
include("https://fantasy-bundesliga.de/php/auth.php");
?>
<html>

<aside>

  <script>
    /* Toggle between adding and removing the "responsive" class to topnav when the user clicks on the icon */
    function responsive_nav() {
      var x = document.getElementById("myTopnav");
      if (x.className === "topnav") {
        x.className += " responsive";
      } else {
        x.className = "topnav";
      }
    } 
  </script>

  <!-- The navigation menu -->
  <div class="topnav" id="myTopnav">
    <a href="https://fantasy-bundesliga.de/index.php" class="active">Home</a>

    <div class="dropdown">
      <button class="dropdownbtn">Liga</button>
      <div class="dropdown-content">
        <a href="https://fantasy-bundesliga.de/html/spieltag.php">Spieltag</a>
        <a href="https://fantasy-bundesliga.de/html/tabelle.php">Tabelle</a>
        <a href="https://fantasy-bundesliga.de/html/pokal.php">Pokal</a>
        <a href="https://fantasy-bundesliga.de/html/draft.php">Draft</a>
        <a href="https://fantasy-bundesliga.de/html/stats.php">Statistiken</a>
      </div>
    </div>

    <div class="dropdown" id="transfermarkt">
      <button class="dropdownbtn" >Transfermarkt</button>
      <div class="dropdown-content">
        <a href="https://fantasy-bundesliga.de/html/transfermarkt.php">Spieler aufnehmen</a>
        <a class="" href="https://fantasy-bundesliga.de/html/waiver.php">Waiver Priorisierung</a>
        <a class="" href="https://fantasy-bundesliga.de/html/waiver_delete.php">Trades & Waivers</a>
        <a href="https://fantasy-bundesliga.de/html/research.php?click_player=1018">Spieler-Datenbank</a>
      </div>
    </div>

    <div class="dropdown">
      <button class="dropdownbtn">Mein Team</button>
      <div class="dropdown-content">
        <?php
          $team_name = strval(mb_convert_encoding($_SESSION["user_teamname"],'UTF-8'));
          echo "<a href='https://fantasy-bundesliga.de/html/mein_team.php?show_team='".$team_name."'.php'>Team verwalten</a>";
        ?>
        <a href="https://fantasy-bundesliga.de/php/redirect-to-active-match.php">Game-Center</a>
      </div>
    </div>

    <a href="https://fantasy-bundesliga.de/html/regelwerk.php" id="regeln">Regeln</a>

    <a href="https://fantasy-bundesliga.de/html/account_verwaltung.php" id="account">Mein Account</a>

    <a href='javascript:void(0);' class="icon" onclick='responsive_nav()'>&#9776;</a>

  </div>
</aside>
</html>