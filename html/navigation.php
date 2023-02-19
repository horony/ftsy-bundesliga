<html>
<?php include('../php/auth.php');?>

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
    <a href="../index.php" class="active">Home</a>

    <div class="dropdown">
      <button class="dropdownbtn">Liga</button>
      <div class="dropdown-content">
        <a href="spieltag.php">Spieltag</a>
        <a href="spielstand.php">Tabelle</a>
        <a href="pokal.php">Pokal</a>
        <a href="draft.php">Draft</a>
        <a href="stats.php">Statistiken</a>
      </div>
    </div>

    <div class="dropdown" id="transfermarkt">
      <button class="dropdownbtn" >Transfermarkt</button>
      <div class="dropdown-content">
        <a href="transfermarkt.php">Spieler aufnehmen</a>
        <a class="" href="waiver.php">Waiver Priorisierung</a>
        <a class="" href="delete_waiver.php">Trades & Waivers</a>
        <a href="research.php?click_player=1018">Spieler-Datenbank</a>
      </div>
    </div>

    <div class="dropdown">
      <button class="dropdownbtn">Mein Team</button>
      <div class="dropdown-content">
        <?php
          $team_name = strval(mb_convert_encoding($_SESSION["user_teamname"],'UTF-8'));
          echo "<a href='mein_team.php?show_team='".$team_name."'.php'>Team verwalten</a>";
        ?>
        <a href="../php/redirect_game_center.php">Game-Center</a>
      </div>
    </div>

    <a href="regelwerk.php" id="regeln">Regeln</a>

    <a href="account_verwaltung.php" id="account">Mein Account</a>

    <a href='javascript:void(0);' class="icon" onclick='responsive_nav()'>&#9776;</a>

  </div>
</aside>
</html>