// Calls php-script to display different fixtures for each cup round

function show_cup_round(str) {

  var xhttp;
  window.clicked_round = $clicked_round;

  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
   		document.getElementById("cup_wrapper").innerHTML = this.responseText;
    }
  };

  xhttp.open("GET", "../php/display-ftsy-cup-fixtures.php?round="+str, true);
  xhttp.withCredentials = true;
  xhttp.send(null);

}