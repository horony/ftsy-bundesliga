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
	var fantasyButton = document.getElementById("button_fantasy_tabelle");
	var bundesligaButton = document.getElementById("button_bundesliga_tabelle");
	
	if (fantasyButton) {
		fantasyButton.style.color = "#4caf50";
	}
	if (bundesligaButton) {
		bundesligaButton.style.color = "white";
	}
}

function changeColorTabelle2(){
	var fantasyButton = document.getElementById("button_fantasy_tabelle");
	var bundesligaButton = document.getElementById("button_bundesliga_tabelle");
	
	if (bundesligaButton) {
		bundesligaButton.style.color = "#4caf50";
	}
	if (fantasyButton) {
		fantasyButton.style.color = "white";
	}
}