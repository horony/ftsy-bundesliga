/* 1. Level Navigation */

function show_sub_nav_1(str) {
  console.log("Klick auf show_sub_nav_1", $(str).text());
  var xhttp;
  window.choice = $choice;
  // console.log("Choice ist", choice);

  // console.log("Opening XMLHttpRequest");
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      // console.log("Lade Ergebnisse ins sub_nav_1"); 
      document.getElementById("sub_nav_1").innerHTML = this.responseText;
    }
  };

  // console.log('Calling php File');
  xhttp.open("GET", "../php/display-topxi-sub-nav-1.php?lvl1="+$choice, true);
  xhttp.withCredentials = true;
  xhttp.send(null);
  //console.log(xhttp);
  //console.log(this.responseText);

  if ($choice == 'BUNDESLIGA-TEAMS'){
    show_sub_nav_2($choice, -1)
  }
}

/* 2. Level Navigation */

function show_sub_nav_2(topic, lvl2) {
  console.log("Klick auf show_sub_nav_2")
  console.log(lvl2, topic)

  var xhttp;
  // console.log("Choice ist", choice);

  // console.log("Opening XMLHttpRequest");
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      // console.log("Lade Ergebnisse ins sub_nav_1");
      document.getElementById("sub_nav_2").innerHTML = this.responseText;
    }
  };

  // console.log('Calling php File');
  xhttp.open("GET", "../php/display-topxi-sub-nav-2.php?topic="+topic+"&lvl2="+lvl2, true);
  xhttp.withCredentials = true;
  xhttp.send(null);
  //console.log(xhttp);
  //console.log(this.responseText);
}