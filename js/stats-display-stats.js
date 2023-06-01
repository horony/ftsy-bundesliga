/* Call php to load data into div */

function showStats(str) {
	var xhttp;
	window.choice = $choice;

	if (str == "") {
   	document.getElementById("content").innerHTML = "";
   	return;
  }
  	
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
   	if (this.readyState == 4 && this.status == 200) {
  		document.getElementById("content").innerHTML = this.responseText;
   	}
  };

	xhttp.open("GET", "../php/display-stats.php?q=ALL&stat="+$choice, true);
  xhttp.withCredentials = true;
  xhttp.send(null);
}

function showPos(str) {
	$clicked_pos = str;
	window.choice = $choice;
	var xhttp;
 	
 	xhttp = new XMLHttpRequest();
  
  xhttp.onreadystatechange = function() {
   	if (this.readyState == 4 && this.status == 200) {
  		document.getElementById("content").innerHTML = this.responseText;
   	}
  };

	xhttp.open("GET", "../php/display-stats.php?q="+$clicked_pos+"&stat="+$choice, true);
  xhttp.withCredentials = true;
  xhttp.send(null);
}