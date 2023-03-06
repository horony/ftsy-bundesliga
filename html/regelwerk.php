<?php include '../php/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
 	<title>FANTASY BUNDESLIGA</title> 

	<meta name="robots" content="noindex">
	<meta charset="UTF-8">   

	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/regelwerk.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  
<body>

<header><h1>FANTASY BUNDESLIGA</h1></header>

<!-- Navigation -->
	<div id = "hilfscontainer">
		<?php include("navigation.php"); ?>
	</div>

<!-- Content -->
	<div id="headline" class="row">
		<h2 style='color:#4caf50; text-align:'center'>REGELWERK<h2>	
	</div>
	
  <p style="margin-bottom:10px">
    <b>Im folgenden erhaltet ihr alle notwendigen Informationen zu Regeln und Abläufen des Spiels</b>
  </p>

	<p>
		<div style="border: 3px grey solid;">
			<u>Navigationsmenü</u>
			<br>
			<br>
	    <a href="#Spielsystem">1.Spielsystem</a><br>
		  <a href="#Punktevergabe">2.Punktevergabe (Teams)</a><br>
		  <a href="#Tiebreaker">3.Tiebreaker</a><br>
		  <a href="#punktespieler">4.Punktevergabe (Spieler)</a><br>
		  <a href="#punktespieler">&emsp;&emsp;4.1 -Live-Punkte (Spieler)</a><br>
		  <a href="#Aufstellung">5.Aufstellung</a><br>
		  <a href="#Spieltag">6.Spieltag</a><br>
		  <a href="#Spieler_aufnehmen">7.Spieler aufnehmen</a><br>
		  <a href="#Free_Agents">&emsp;&emsp;7.1.Freie Spieler aufnehmen</a><br>
		  <a href="#Waiver">&emsp;&emsp;7.2.Waiver aufnehmen</a><br>
		  <a href="#Trade">&emsp;&emsp;7.3.Spieler mit Mitspielern tauschen</a><br>
		  <a href="#Anti-Cheating">8.Anti-Cheating</a><br>
	  </div>
 	</p>
   
  <h1 id="Spielsystem">1. Spielsystem</h1>
  <p style="margin-bottom:30px">
    Genau wie in der Bundesliga, ist das Ziel dieses Spiels, am Ende der Saison die meisten Punkte von allen Mitspielern zu haben und somit zu gewinnen. 
	  Um Punkte zu erreichen, werden direkte Duelle zwischen den Kontrahenten ausgespielt. Jedes Team tritt Woche für Woche gegen ein anderes Team: z.B. Team 1 vs. Team 4, Team 3 vs. Team 10 usw. Dementsprechend gibt es einen Spielplan wer wann gegen wen spielt. Diesen findet ihr auf dem Reiter <b>Liga</b>, im Unterpunkt <b>Spieltag</b>.
  </p>

	<h1 id="Punktevergabe">2. Punktevergabe (Teams)</h1>
  <p style="margin-bottom:30px">
    Das Team mit den meisten Punkten im direkten Duell gewinnt das Spiel und erhält 3 Punkte, wie in der Bundesliga. Ein Unentschieden bringt euch 1 Punkt, eine Niederlage 0. Außerdem gibt es für den punktstärksten Verlierer, den Lucky Loser, einen Trostpunkt.<br> Einen Zwischenstand eures aktuellen Spiels könnt ihr live beobachten. Dazu müsst ihr über den Reiter <b>Liga</b> zum Unterpunkt <b>Game-Center</b> navigieren.<br>
	  Basierend auf diesen Punkten wird dann die Tabelle berechnet, sobald alle Bundesliga-Spiele des Spieltags abgeschlossen sind. 
	</p>

	<h1 id="Tiebreaker">3. Tiebreaker</h1>
  <p style="margin-bottom:30px">
  	Im Gegensatz zur Bundesliga wird bei einem Gleichstand nicht die Tor- oder Punktedifferenz als entscheidender Faktor berechnet, sondern der <b>(1) direkte Vergleich</b>.<br> Sollte dieser auch Unentschieden sein, wird als nächstes der Wert der <b>(2) insgesamt erzielten Punkte</b> gewertet.<br>
	  Erst in dem unwahrscheinlichen Szenario, dass auch dies gleich ist, werden die <b>(3) kassierten Punkte</b> als letzter Tiebreaker gewertet, positiv für den der die meisten Punkte kassiert hat. 
	</p>

	<h1 id="punktespieler">4. Punktevergabe (Spieler)</h1>
  <p style="margin-bottom:30px">
    Die Punkte die jeder Spieler an einem Spieltag erhält, errechnen sich wie folgt: <br>
    <img src="../img/punktetabelle.JPG"><br>
  </p>

 	<h1 id="Aufstellung">&emsp;4.1. Live-Punkte</h1>
  <p style="margin-bottom:30px">
    &emsp;&emsp;Auf <b> Liga --> Game-Center </b> könnt ihr während des laufenden Spieltages live die Punkte eurer Spieler verfolgen. Die Punkte werden alle 5 Minuten aktualisiert. <br> 
    &emsp;&emsp;Im Anschluss werden sie addiert und ihr könnt eure insgesamten Live-Punkte oben rechts neben eurem Teamnamen einsehen. Das gleiche gilt für eueren Gegner. <br>
  	&emsp;&emsp;Bitte beachtet, dass es sich bei den dort angezeigten Punkten um Live-Punkte handelt, und sich die finite Endnote noch einmal verändern kann, z.B. durch einen Zusatzpunkt für die weiße Weste o.Ä. <br>
  	&emsp;&emsp;<i>Am ersten Spieltag kann es zu Verzögerungen oder einem Totalausfall des Live-Punkte-Systems kommen, da wir keine Möglichkeit hatten, das Feature zu testen. </i>
  </p>
	   
	<h1 id="Aufstellung">5. Aufstellung</h1>
  <p style="margin-bottom:30px">
    Auf eurer Aufstellungsseite, die ihr unter <b>Mein Team --> Meine Aufstellung</b> findet, wählt ihr dann für jeden Spieltag individuell 11 Spieler aus. 
		Auf der linken Seite findet ihr ein kleines Drop-Down Feld, in dem ihr zwischen sieben unterschiedlichen Formationen auswählen könnt. Wenn ihr eine Formation auswählt, müsst ihr alle Felder mit Spielern besetzen um 11 Spieler auf dem Platz zu haben. <br>
		Spieler die schon gespielt haben, zu dem Zeitpunkt als sie sich in eurer Aufstellung befunden haben, sind nicht mehr auswechselbar. Spieler die zu dieser Zeit auf eurer Bank waren, können nicht mehr eingewechselt werden.<br>
	  Auf der rechten Seite findet ihr einen Knopf mit dem Titel <b>"Aufstellung absenden"</b>. Sobald ihr diesen betätigt wird eurer Aufstellung an das System gesendet. Bei jeder Änderung die ihr durchführt, muss der Knopf noch einmal gedrückt werden. <br>
	  Wenn ihr eure Aufstellung abgesendet habt, werden diese Spieler auch im <b>Game-Center</b> als eure aktive Mannschaft dargestellt. 
	</p>
	  
	  
	<h1 id="Spieltag">6. Spieltag</h1>
  <p style="margin-bottom:30px">
	  Nachdem ihr eure Aufstellung übermittelt habt, könnte ihr eure aktive Aufstellung im Game-Center einsehen. Navigiert dazu über <b> Liga --> Game-Center </b>. Dort könnt ihr auch die Aufstellung eures Gegners einsehen. <br>
	  <i>Im Moment könnt ihr nur euer eigenes Match einsehen. Zukünftig werdet ihr auf <b>Liga --> Spieltag </b> auch alle anderen Matches einsehen können. </i> Wir arbeiten ständig an visuellen und benutzerfreundlichen Updates. Sobald diese fertig sind, werden wir sie implementieren und so besonders die Game-Center Seite noch ein wenig schmackhafter zu machen. 
  </p> <br>
	  
	<h1 id="Spieler_aufnehmen">7. Spieler aufnehmen</h1>
  <p style="margin-bottom:30px">
	  Im Vergleich zu unserem Beta-Test ist die Aufnahme neuer Spieler nun viel intuitiver und ihr könnt alles selbst erledigen. Unter dem Reiter <b>Transfermarkt</b> findet ihr eine Auswahl an verschiedenen Optionen.<br>
		Unter dem Reiter <b>Spieler-Datenbank</b> findet ihr alle erdenklichen Informationen und Statistiken von allen Spielern im Spiel. Hier könnt ihr euch zum Beispiel über die Punkte, den Vergleich zu anderen Spielern auf der selben Position und vieles mehr informieren. <br>
		Wenn ihr euch dann einen Spieler ausgeguckt habt, den ihr gerne in eurem Team haben wollt, navigiert dazu auf den Reiter <b>Spieler aufnehmen</b>. Dort angekommen seht ihr auf der linken Seite alle Spieler, die nicht bereits in eurem Team sind (eure Spieler seht ihr auf der rechten Seite).
		Zugehörig zu jedem Spieler findet ihr eine Check-Box, die den Spieler auswählt, den ihr haben wollt. Genau so verhält es sich mit dem Spieler den ihr für diesen abgeben wollt. <b> Denkt daran, jedes mal wenn ihr einen Spieler aufnehmen wollt, müsst ihr einen dafür abgeben. Euer Team ist auf 16 Spieler begrenzt</b>.
		Wenn ihr auf beiden Seiten einen Spieler ausgewählt habt, könnt ihr unten auf <b>Transferanfrage abschicken</b> drücken. <br>
		Nicht alle Spieler sind allerdings frei aufzunehmen. Die Spieler sind in drei verschiedene Klassen unterteilt, die ihr in der Tabelle unter dem Reiter <i>Besitzer</i> einsehen könnt. In den folgenden Unterpunkten wird noch einmal genauer erklärt wie sich jede Klasse bei einer Transferanfrage verhält. 
	</p>
	   
	<h1 id="Free_Agents">&emsp;7.1. Freie Spieler aufnehmen (Free Agents)</h1>
  <p style="margin-bottom:30px">
		<div style="text-indent:20px;">
      Wenn der Spieler in der <i>Besitzer</i> Spalte als <b>Free Agent</b> gekennzeichnet ist, könnt ihr den Spieler ohne Probleme aufnehmen. Dazu wählt ihr die Check-Box neben seinem Namen aus, macht das gleiche mit dem Spieler den ihr dafür abgeben wollt und drückt auf <b>Transferanfrage abschicken</b>. Der Spieler ist direkt in eurem Team und ihr könnt ihn umgehend aufstellen.
	  </div>
	</p>
	
	<h1 id="Waiver">&emsp;7.2. Waiver aufnehmen</h1>
  <p style="margin-bottom:30px">
    &emsp;&emsp;Wenn der Spieler in der <i>Besitzer</i> Spalte als <b>Waiver</b> gekennzeichnet ist, befindet er sich in einem über den Spieltag gesperrten Pool. Über <b>Transferanfrage abschicken</b> erstellt ihr also einen Waiver Eintrag und erhaltet den Spieler erst zum nächsten Waiver Termin und vorausgesetzt niemand mit einer höheren Waiver Position schnappt ihn euch vor der Nase weg. <br>
  	Das Waiver-System funktioniert wie folgt: Auf Spieler die am aktiven Spieltag schon gespielt haben, (auch wenn sie nur auf der Bank saßen) müsst ihr euch „bewerben“ und habt dafür Zeit bis bis <u>Dienstags um 11:00 Uhr</u>. Das gleiche passiert mit den Spielern die ihr abgebt. Die Spieler die bei der Deadline abgegeben werden, können bei einer zweiten Deadline aufgenommen werden. Diese ist Freitags um 11:00 Uhr. Die Spieler die an diesem Tag abgegeben werden, wandern wieder in den Dienstags-Waiver.
		Basierend auf eurer Tabellenposition habt ihr eine Waiverposition die durch die Tabelle bestimmt wird. Der Letzte der Tabelle hat die erste Wahl und von da aus geht es weiter nach oben. Wenn wir einmal durch sind, geht es in der gleichen Reihenfolge wieder durch. Die Tabelle sowie die Waiverposition findet ihr unter <b>Liga --> Spielstand </b>.<br>
		Falls euer Waiver erfolgreich war, erhaltet ihr dann den Spieler zu der oben angegeben Uhrzeit. <br>
		Da man auch mehrere Waiver abschicken kann, könnt ihr unter <b>Transfermarkt --> Waiver Priorisierung</b> auswählen, welchen Spieler ihr am liebsten haben wollt, und diesen an die erste Priorität im System setzen. Dazu schiebt ihr die Spieler per Drag and Drop and die gewünschte Position. Für den Fall, dass ihr einen Waiver-Eintrag löschen wollt, navigiert ihr zu <b>Transfermarkt --> Trades & Waivers</b>. Dort könnt ihr den Eintrag problemlos per Knopfdruck löschen.
  </p>

	<h1 id="Trade">&emsp;7.3. Spieler mit Mitspielern tauschen</h1>
  <p style="margin-bottom:30px">
    &emsp;&emsp;&emsp;&emsp;Wenn der Spieler in der <i>Besitzer</i> Spalte einen anderen Manager auflistet, befindet er sich im Team dieses Spielers. Über <b>Transferanfrage abschicken</b> erstellt ihr also einen Trade Anfrage. <br>
	  Der Spieler dem ihr diese Anfrage gesendet habt, erhält darüber umgehend eine rot leuchtende Nachricht auf der rechten Seite der Startseite. Über den darunterstehenden Link gelangt man zu <b>Transfermarkt --> Trades & Waivers</b> wo jeder Spieler seine eingegangen Trade-Anfragen verwalten kann, sowie die ausgehenden Anfragen überblicken und zurückziehen kann. Falls ein Spieler eine Trade Anfrage annimmt, wird der Transfer automatisch durch das System durchgeführt. <i> Eine Ausnahme ist allerdings das Spieler die am aktiven Spieltag bereits gespielt haben, nicht getradet werden können. Dies bedeutet, dass wenn ihr einen Trade durchführen wollt, den ihr am nächsten Spieltag noch einsetzten wollt, müssen beide Spieler noch nicht gespielt haben. </i> 
  </p>
	
	<h1 id="Anti-Cheating">8. Anti-Cheating</h1>
  <p style="margin-bottom:30px">
		Spaß beiseite; im Idealfall sollte es keine Möglichkeit geben, um zu cheaten. Falls jemand doch in einer Situation ein Schlupfloch oder einen Fehler finden sollte, bitten wir euch ausdrücklich dies mitzuteilen. Falls wir herausfinden, dass jemand ein solches Schlupfloch ausgenutzt hat, wird der Spieler umgehend vom Spiel ausgeschlossen. 
	</p>
  
  <p>
    <br>
	  <br>
	  Du möchtest dir dieses spannende Dokument noch einmal durchlesen? <a href="./regelwerk.php">Zurück zum Anfang springen</a>.
  </p>
</div>
</body>
</html>