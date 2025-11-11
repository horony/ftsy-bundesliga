<?php include '../php/auth.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <title>FANTASY BUNDESLIGA</title> 

    <!-- Meta Tags -->
    <meta name="robots" content="noindex">
    <meta charset="UTF-8">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../dev/css/regelwerk.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/overall.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    
    <!-- External Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
</head>
<body>

<!-- Header image -->
  <header>
    <?php require "header.php"; ?>
  </header>

<!-- Navigation -->
  <div id="hilfscontainer">
    <?php include("../html/navigation.php"); ?>
  </div>

<!-- Content Section -->
<main class="content-wrapper">
  <!-- Left Navigation Menu -->
  <div class="content-left">
    <nav class="navigation-menu">
      <ul>
        <li><a href="#Spielsystem">1. Spielsystem</a></li>
        <li><a href="#Punktevergabe">2. Punktevergabe (Teams)</a></li>
        <li><a href="#Tiebreaker">3. Tiebreaker</a></li>
        <li><a href="#punktespieler">4. Punktevergabe (Spieler)</a></li>
        <li><a href="#Live-Punkte">4.1 Live-Punkte (Spieler)</a></li>
        <li><a href="#Aufstellung">5. Aufstellung</a></li>
        <li><a href="#Spieltag">6. Spieltag</a></li>
        <li><a href="#Spieler_aufnehmen">7. Spieler aufnehmen</a></li>
        <li><a href="#Free_Agents">7.1 Freie Spieler aufnehmen</a></li>
        <li><a href="#Waiver">7.2 Waiver aufnehmen</a></li>
        <li><a href="#Trade">7.3 Spieler mit Mitspielern tauschen</a></li>
        <li><a href="#Anti-Cheating">8. Anti-Cheating</a></li>
      </ul>
    </nav>
  </div>

  <!-- Right Side Content -->
  <div class="content-right">
    <section class="headline">
      <h2>REGELWERK</h2>
      <p><b>Im folgenden erhaltet ihr alle notwendigen Informationen zu Regeln und Abläufen des Spiels</b></p>
    </section>

    <section id="Spielsystem" class="section-content">
      <h3>1. Spielsystem</h3>
      <p>  Genau wie in der Bundesliga, ist das Ziel dieses Spiels, am Ende der Saison die meisten Punkte von allen Mitspielern zu haben und somit zu gewinnen. 
    Um Punkte zu erreichen, werden direkte Duelle zwischen den Kontrahenten ausgespielt. Jedes Team tritt Woche für Woche gegen ein anderes Team: z.B. Team 1 vs. Team 4, Team 3 vs. Team 10 usw. Dementsprechend gibt es einen Spielplan wer wann gegen wen spielt. Diesen findet ihr auf dem Reiter <b>Liga</b>, im Unterpunkt <b>Spieltag</b>.</p>
    </section>

    <section id="Punktevergabe" class="section-content">
      <h3>2. Punktevergabe (Teams)</h3>
      <p> Das Team mit den meisten Punkten im direkten Duell gewinnt das Spiel und erhält 3 Punkte, wie in der Bundesliga. Ein Unentschieden bringt euch 1 Punkt, eine Niederlage 0. Außerdem gibt es für den punktstärksten Verlierer, den Lucky Loser, einen Trostpunkt.<br> Einen Zwischenstand eures aktuellen Spiels könnt ihr live beobachten. Dazu müsst ihr über den Reiter <b>Mein Team</b> zum Unterpunkt <b>Game-Center</b> navigieren.<br>
    Basierend auf diesen Punkten wird dann die Tabelle berechnet, sobald alle Bundesliga-Spiele des Spieltags abgeschlossen sind. </p>
    </section>

    <section id="Tiebreaker" class="section-content">
      <h3>3. Tiebreaker</h3>
      <p>Im Gegensatz zur Bundesliga wird bei einem Gleichstand nicht die Tor- oder Punktedifferenz als entscheidender Faktor berechnet, sondern der <b>(1) direkte Vergleich</b>.<br> Sollte dieser auch Unentschieden sein, wird als nächstes der Wert der <b>(2) insgesamt erzielten Punkte</b> gewertet.<br>
    Erst in dem unwahrscheinlichen Szenario, dass auch dies gleich ist, werden die <b>(3) kassierten Punkte</b> als letzter Tiebreaker gewertet, positiv für den der die meisten Punkte kassiert hat. </p>
    </section>

    <section id="punktespieler" class="section-content">
      <h3>4. Punktevergabe (Spieler)</h3>
      <p>Die Punkte die jeder Spieler an einem Spieltag erhält, errechnen sich wie folgt:</p>
      <img src="../dev/img/punktetabelle.JPG" alt="Punktetabelle">
    </section>

   <section id="Live-Punkte" class="section-content">
    <h3>4.1 Live-Punkte</h3>
    <p>
    Auf <b> Liga > Game-Center </b> könnt ihr während des laufenden Spieltages live die Punkte eurer Spieler verfolgen. Die Punkte werden alle 3 Minuten aktualisiert. <br>
    Im Anschluss werden sie addiert und ihr könnt eure insgesamten Live-Punkte oben rechts neben eurem Teamnamen einsehen. Das gleiche gilt für eueren Gegner. <br>
    Bitte beachtet, dass es sich bei den dort angezeigten Punkten um Live-Punkte handelt, und sich die finiten Endpunkte noch einmal verändern können. 
    </p>
    <p>
    Eurer Gesamtpunkte werden in einem grünen Balken dargestellt, der mit den Punkten live mitwächst. <br>
    
    Die Punkte, die ein jeder Spieler erzielt, werden am Ende einer jeden Spielerzeile dargestellt und sind je nach Punkten farblich hinterlegt.<br>
    Die Prognose hat einen eigenen Platz neben den tatsächlichen Punkten. Die Prognose passt sich dem Spielstatus an: <br>
    Ist das Spiel noch nicht angepfiffen, ist die Prognose statisch, gemessen an Punkten aus den vergangenen Spielen sowie der durchschnittlichen zugelassenen Punkte des Vereins, gegen den der Spieler am Spieltag spielt. <br>
    <i>Ist ein Spieler verletzt oder gesperrt, bzw. hat in den letzten Wochen nicht gespielt, wird die Prognose zu einer roten Null, um euch direkt darauf aufmerksam zu machen.</i> <br>
    Während das Spiel live ist, verändert sich die Prognose. Sie stellt nun nicht mehr die statische Prognose vorm Spiel dar, sondern wird zur Live-Prognose. Die mathematische Formel zur Berechnung lautet:
    </p>
    <p>
    <b>Liveprognose = Aktueller Score + (Prognose - 4) / 90 * Verbleibende Minuten</b>
    </p>
    <p>
    Auf Deutsch heißt das, dass wir anteilig die live erzielten Punkte des Spielers gegen die statistische Prognose gegenrechnen. Ein einfaches Beispiel:
    </p>
    <p>
    Spieler X hat eine Prognose von 20 Punkten (8 pro Halbzeit + 4 für Aufstellung), und steht zur Halbzeit bei 15 Punkten. Für die Live-Prognose rechnen wir nun 45/90 oder aber die Hälfte der Prognose minus der 4 Punkte, die der Spieler für das Betreten des Feldes erhält, da diese ja bereits gutgeschrieben wurden und in der 2. HZ nicht mehr gewichtet werden:
    </p>
    <p>
    <b>15 + (20 - 4) / 90 * 45 = 23 Punkte Liveprognose</b>
    </p>
    <p>
    Somit bekommt ihr eine statistische Einschätzung, wie viele Punkte der Spieler am Ende des Spiels haben wird. <br>
    Wenn das Spiel vorbei ist, wird die Live-Prognose wieder durch die statische Prognose ersetzt und zeigt euch, ob euer Spieler über- oder unterperformt hat.
    </p>
    <p>
    Basierend auf den erklärten Werten, setzt sich die Gesamtprognose zusammen:
    </p>
    <p>
    <b>Gesamtprognose = (Punkte, if FINAL) + (Liveprognose, if LIVE) + (Prognose, if not started)</b>
    </p>
    <p>
    Auch wieder auf Deutsch heißt das, dass die Gesamtprognose die Live-Prognose der Spieler, die gerade spielen, addiert wird mit den Punkten der Spieler, die schon durch sind, und der Prognose der Spieler, die noch nicht gespielt haben. Daraus ergibt sich für euch eine so live wie möglich gehaltene Kalkulation der Punkte, die ihr nach statistischer Erwartung am Ende des Spieltags haben solltet. So ist ein Vergleich zu eurem Gegner einfach und übersichtlich möglich, auch wenn ihr eine ungleiche Anzahl an Spielern auf dem Feld habt/hattet.
    </p>
    <h4>Limitationen der Live-Prognose, bitte beachten:</h4>
    <ul>
    <li>Die Formel geht davon aus, dass der Spieler seine prognostizierte Leistung gleichmäßig über 90 Minuten erbringt. In der Realität passiert das nicht immer – manche Spieler haben z. B. eine starke erste Halbzeit und eine schwächere zweite (oder umgekehrt).</li>
    <li>Wenn ein Spieler in der ersten Halbzeit überperformt, kann die Prognose zu hoch ausfallen.</li>
    <li>Wenn ein Spieler einen Sahne- bzw. Scheißtag erwischt, erwartet die Live-Prognose, dass er im Verlaufe des Spiels wieder in Richtung seiner Normalform geht, was natürlich nicht immer zwingend so ist.</li>
    <li>Aktuell haben wir keine Möglichkeit, live zu tracken, ob ein Spieler bereits ausgewechselt ist oder nicht. Entsprechend kalkuliert das System auch bei einem ausgewechselten Spieler weiter.</li>
    <li>Das Gleiche gilt für einen Spieler, der erst im Laufe des Spiels eingewechselt wird. Hier wertet er die Spielminuten so, als ob er in der Startaufstellung war und prognostiziert von dort 90 Minuten.</li>
    </ul>
    <p>Aber da ja immer alle fein ihre Aufstellung machen, sollte das ja kein Thema sein.</p>
  </section>

    <section id="Aufstellung" class="section-content">
      <h3>5. Aufstellung</h3>
      <p> Auf eurer Aufstellungsseite, die ihr unter <b>Mein Team --> Meine Aufstellung</b> findet, wählt ihr dann für jeden Spieltag individuell 11 Spieler aus. 
    Auf der linken Seite findet ihr ein kleines Drop-Down Feld, in dem ihr zwischen sieben unterschiedlichen Formationen auswählen könnt. Wenn ihr eine Formation auswählt, müsst ihr alle Felder mit Spielern besetzen um 11 Spieler auf dem Platz zu haben. <br>
    Spieler die schon gespielt haben, zu dem Zeitpunkt als sie sich in eurer Aufstellung befunden haben, sind nicht mehr auswechselbar. Spieler die zu dieser Zeit auf eurer Bank waren, können nicht mehr eingewechselt werden.<br>
    Wenn ihr eure Aufstellung abgesendet habt, werden diese Spieler auch im <b>Game-Center</b> als eure aktive Mannschaft dargestellt. </p>
    </section>

    <section id="Spieltag" class="section-content">
      <h3>6. Spieltag</h3>
      <p>NNachdem ihr eure Aufstellung übermittelt habt, könnte ihr eure aktive Aufstellung im Game-Center einsehen. Navigiert dazu über <b> Liga > Game-Center </b>. Dort könnt ihr auch die Aufstellung eures Gegners einsehen. <br>
    <i>Auf <b>Liga > Spieltag </b> könnt ihr alle anderen Matches einsehen. </i> </p>
    </section>

    <section id="Spieler_aufnehmen" class="section-content">
      <h3>7. Spieler aufnehmen</h3>
      <p>Unter dem Reiter <b>Transfermarkt</b> findet ihr eine Auswahl an verschiedenen Optionen.<br>
    Unter dem Reiter <b>Spieler-Datenbank</b> findet ihr alle erdenklichen Informationen und Statistiken von allen Spielern im Spiel. Hier könnt ihr euch zum Beispiel über die Punkte, den Vergleich zu anderen Spielern auf der selben Position und vieles mehr informieren. <br>
    Wenn ihr euch dann einen Spieler ausgeguckt habt, den ihr gerne in eurem Team haben wollt, navigiert dazu auf den Reiter <b>Spieler aufnehmen</b>. Dort angekommen seht ihr auf der linken Seite alle verfügbaren Spieler, eure Spieler seht ihr auf der rechten Seite.
    Zugehörig zu jedem Spieler findet ihr eine Check-Box, die den Spieler auswählt, den ihr haben wollt. Genau so verhält es sich mit dem Spieler den ihr für diesen abgeben wollt. <b> Denkt daran, jedes mal wenn ihr einen Spieler aufnehmen wollt, müsst ihr einen dafür abgeben. Euer Team ist auf 16 Spieler begrenzt</b>.
    Wenn ihr auf beiden Seiten einen Spieler ausgewählt habt, könnt ihr unten auf <b>Transferanfrage abschicken</b> drücken. <br>
    Nicht alle Spieler sind allerdings frei aufzunehmen. Die Spieler sind in drei verschiedene Klassen unterteilt, die ihr in der Tabelle unter dem Reiter <i>Besitzer</i> einsehen könnt. In den folgenden Unterpunkten wird noch einmal genauer erklärt wie sich jede Klasse bei einer Transferanfrage verhält. </p>
    </section>

    <section id="Free_Agents" class="section-content">
      <h3>7.1 Freie Spieler aufnehmen</h3>
      <p>Wenn der Spieler in der <i>Besitzer</i> Spalte als <b>Free Agent</b> gekennzeichnet ist, könnt ihr den Spieler ohne Probleme aufnehmen. Dazu wählt ihr die Check-Box neben seinem Namen aus, macht das gleiche mit dem Spieler den ihr dafür abgeben wollt und drückt auf <b>Transferanfrage abschicken</b>. Der Spieler ist direkt in eurem Team und ihr könnt ihn umgehend aufstellen.</p>
    </section>

    <section id="Waiver" class="section-content">
      <h3>7.2 Waiver aufnehmen</h3>
      <p>Wenn der Spieler in der <i>Besitzer</i> Spalte als <b>Waiver</b> gekennzeichnet ist, befindet er sich in einem über den Spieltag gesperrten Pool. Über <b>Transferanfrage abschicken</b> erstellt ihr also einen Waiver Eintrag und erhaltet den Spieler erst zum nächsten Waiver Termin und vorausgesetzt niemand mit einer höheren Waiver Position schnappt ihn euch vor der Nase weg. <br>
    Das Waiver-System funktioniert wie folgt: Auf Spieler die am aktiven Spieltag schon gespielt haben, (auch wenn sie nur auf der Bank saßen) müsst ihr euch „bewerben“ und habt dafür in der Regel Zeit bis  <u>Mittwoch um 12:00 Uhr</u>. Das gleiche passiert mit den Spielern die ihr abgebt. Die Spieler die bei der Deadline abgegeben werden, können bei einer zweiten Deadline aufgenommen werden. Diese ist Freitags um 12:00 Uhr. Die Spieler die an diesem Tag abgegeben werden, wandern wieder in den Mittwochs-Waiver.
    Basierend auf eurer Tabellenposition habt ihr eine Waiverposition die durch die Tabelle bestimmt wird. Der Letzte der Tabelle hat die erste Wahl und von da aus geht es weiter nach oben. Wenn wir einmal durch sind, geht es in der gleichen Reihenfolge wieder durch. Die Tabelle sowie die Waiverposition findet ihr unter <b>Liga > Spielstand </b>.<br>
    Falls euer Waiver erfolgreich war, erhaltet ihr dann den Spieler zu der oben angegeben Uhrzeit. <br>
    Da man auch mehrere Waiver abschicken kann, könnt ihr unter <b>Transfermarkt > Waiver Priorisierung</b> auswählen, welchen Spieler ihr am liebsten haben wollt, und diesen an die erste Priorität im System setzen. Dazu schiebt ihr die Spieler per Drag and Drop and die gewünschte Position. Für den Fall, dass ihr einen Waiver-Eintrag löschen wollt, navigiert ihr zu <b>Transfermarkt > Trades & Waivers</b>. Dort könnt ihr den Eintrag problemlos per Knopfdruck löschen.</p>
    </section>

    <section id="Trade" class="section-content">
      <h3>7.3 Spieler mit Mitspielern tauschen</h3>
      <p>Wenn der Spieler in der <i>Besitzer</i> Spalte einen anderen Manager auflistet, befindet er sich im Team dieses Spielers. Über <b>Transferanfrage abschicken</b> erstellt ihr also einen Trade Anfrage. <br>
    Der Spieler dem ihr diese Anfrage gesendet habt, erhält darüber umgehend eine rot leuchtende Nachricht auf der rechten Seite der Startseite. Über den darunterstehenden Link gelangt man zu <b>Transfermarkt > Trades & Waivers</b> wo jeder Spieler seine eingegangen Trade-Anfragen verwalten kann, sowie die ausgehenden Anfragen überblicken und zurückziehen kann. Falls ein Spieler eine Trade Anfrage annimmt, wird der Transfer automatisch durch das System durchgeführt. <i> Eine Ausnahme ist allerdings das Spieler die am aktiven Spieltag bereits gespielt haben, nicht getradet werden können. Dies bedeutet, dass wenn ihr einen Trade durchführen wollt, den ihr am nächsten Spieltag noch einsetzten wollt, müssen beide Spieler noch nicht gespielt haben. </i> 
  </p>
    </section>

    <section id="Anti-Cheating" class="section-content">
      <h3>8. Anti-Cheating</h3>
      <p>Im Idealfall sollte es keine Möglichkeit geben, um zu cheaten. Falls jemand doch in einer Situation ein Schlupfloch oder einen Fehler finden sollte, bitten wir euch ausdrücklich dies mitzuteilen. Falls wir herausfinden, dass jemand ein solches Schlupfloch ausgenutzt hat, wird der Spieler umgehend vom Spiel ausgeschlossen. </p>
    </section>

    <footer>
      <p><a href="regelwerk.php">Zurück zum Anfang springen</a></p>
    </footer>
  </div>
</main>

</body>
</html>