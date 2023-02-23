// Open modal displaying available substitutions

function changePlayer(elem){

	window.clicked_player = $(elem).data("id");  		
	var xhttp_var;
	
	if (elem == "") {
	    document.getElementById("modal-content").innerHTML = "";
	    return;
	}
	
	xhttp_var = new XMLHttpRequest();
	xhttp_var.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("modal-content").innerHTML = this.responseText;
	    }
	};

	xhttp_var.open("GET", "../php/display-possible-subs.php?clicked_player="+clicked_player, true);
	xhttp_var.withCredentials = true;
	xhttp_var.send("clicked_player"+clicked_player);  

  var modal = document.getElementById("myModal");
  modal.style.display = "block";

  var close_btn = document.getElementById("modal_close");
 
	close_btn.onclick = function() {
		modal.style.display = "none";
	}

	window.onclick = function(event) {
		if (event.target == modal) {
	    modal.style.display = "none";
		}
	}

	$('.player_table2 tr.tr_MF').hide();
}

// Flash animation

var time_flash;

function flash(){
	if (active_view == 'Graph') {
		$('.player_card_item').each(function() {
			var test_id = $(this).data("id");
    		if (test_id == clicked_player_sub || test_id == clicked_player) {
    			$(this).addClass('flash');
    	 	}
    	});
    } else {
	
    }				
}

function flash_exe() {
	time_flash = setTimeout(flash, 500);
}

// Change player from modal

function executeChangePlayer(data){
	window.clicked_player_sub = $(data).data("id");

	$.ajax({
		traditional: true,
		type: "POST",
		url: "../php/jobs/execute-ftsy-sub.php",
		data: ({ click_1: clicked_player, click_2: clicked_player_sub }),
		success: function(response){
			if (active_view == 'Graph') {
				showGraphic();
				flash_exe();
			} else {
				showTable();
				flash_exe();
			}
			if (response != 'Spieler eingewechselt!'){
				alert(response); 
			}
		}
	});

	var modal = document.getElementById("myModal");
	modal.style.display = "none";
}

$('.insideLink').click(function(event){
	event.stopImmediatePropagation();
});

window.addEventListener("load", function(){
	showGraphic();
	changeColorGraphic();  
});
