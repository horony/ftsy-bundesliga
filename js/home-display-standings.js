//Show Fantasy standings
function showFantasyTabelle(str) { 
    var xhttp;
    if (str == "") {
        document.getElementById("tabellen").innerHTML = "";
        return;
    }
    xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
        document.getElementById("tabellen").innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "php/get-current-fantasy-standings.php?q="+str, true);
    xhttp.send();
}

// Change colors considering which elements (Bundesliga or Fantasy) is currently clicked
function changeColorTabelle1(){
	document.getElementById("button_fantasy_tabelle").style.color = "#4caf50";
	document.getElementById("button_bundesliga_tabelle").style.color = "white";
}

function changeColorTabelle2(){
	document.getElementById("button_bundesliga_tabelle").style.color = "#4caf50";
	document.getElementById("button_fantasy_tabelle").style.color = "white";
}