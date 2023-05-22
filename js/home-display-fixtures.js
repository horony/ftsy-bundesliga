// Show Bundesliga fixtures and scores
function showBundesliga(str) {
  var xhttp;
  if (str == "") {
    document.getElementById("boxscores").innerHTML = "";
    return;
  }
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    document.getElementById("boxscores").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "php/get-current-bundesliga-fixtures.php?q="+str, true);
  xhttp.send();
}

// Show Fantasy fixtures and scores
function showFantasy(str) {
  var xhttp;
  if (str == "") {
    document.getElementById("boxscores").innerHTML = "";
    return;
  }
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    document.getElementById("boxscores").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "php/get-current-fantasy-fixtures.php?q="+str, true);
  xhttp.send();
}

// Change colors considering which elements (Bundesliga or Fantasy) is currently clicked
function changeColorScores1(){
	document.getElementById("button_fantasy_scores").style.color = "#4caf50";
	document.getElementById("button_bundesliga_scores").style.color = "white";
}

function changeColorScores2(){
	document.getElementById("button_bundesliga_scores").style.color = "#4caf50";
	document.getElementById("button_fantasy_scores").style.color = "white";
}
