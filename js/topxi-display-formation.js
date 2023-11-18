/* Call php to load data into div */

function show_topxi(topic, var1, var2, var3) {
  console.log('Rufe show_topxi.js auf');

	var xhttp;
	window.choice = $choice;
  console.log('Choice', choice);
  console.log('Vars', topic, var1, var2, var3);
  	
  xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
   	if (this.readyState == 4 && this.status == 200) {
  		document.getElementById("content").innerHTML = this.responseText;
   	}
  };

	xhttp.open("GET", "../php/display-topxi.php?topic="+topic+"&q1="+var1+"&q2="+var2+"&q3="+var3, true);
  xhttp.withCredentials = true;
  xhttp.send(null);
}