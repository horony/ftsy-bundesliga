<?php
//include auth.php file on all secure pages
include("auth.php");
?>

<html>
<head>
    <title>FANTASY BUNDESLIGA</title> 
    <link rel="stylesheet" type="text/css" media="screen, projection" href="css/stylesheet_nav.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="css/stylesheet_draft.css">
    <meta name="robots" content="noindex">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>

<script>

//Suchfunktion

$(document).ready(function(){
  $("#search_player").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    //alert(value);
    $(".players_tr").filter(function() {
    	//alert($("#td_spieler_name").text());
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)

    });
  });
});	

var php_session_user_id = <?php echo $_SESSION['user_id']; ?>;

if(typeof(EventSource) !== "undefined") {

	var source = new EventSource("sse/sse_picked_players.php");
	source.onmessage = function(event) {
	    var jdata_picked_players = JSON.parse(event.data);
	    //console.log(jdata_picked_players);	  	

  		if (jdata_picked_players[0] == 'running'){

		  	var array_length = Object.keys(jdata_picked_players).length;
			var array_length = array_length - 1;
			jdata_picked_players.shift();

		  	const parent = document.getElementById("top_flow_band");
			while (parent.firstChild) {
	    		parent.firstChild.remove();
			}

			var array_highlight_pick_ids = [];       

			for (var i = 0; i < array_length; i++){
				div_element = document.createElement("div");
				id_name = 'highlight_pick_no_' + i.toString();
				div_element.id = id_name;
				div_element.className = "highlight_pick";
				document.getElementById("top_flow_band").appendChild(div_element);
				array_highlight_pick_ids.push(id_name);
			} 

			var elements = document.getElementsByClassName("highlight_pick"); 
				for (var k = 0; k < elements.length; k++) { 
					spieler_nachname = decodeURIComponent(jdata_picked_players[k][3]);
					if (spieler_nachname === ''){
						spieler_nachname = 'Pick ' + jdata_picked_players[k][1];
					}
					team = jdata_picked_players[k][2];
					var bild = jdata_picked_players[k][4];
					
					if (php_session_user_id == jdata_picked_players[k][5]){
						border_color = 'red';
					} else {
						border_color = 'black';
					}

					var html_script = "<div class='highlight_pick_wrap' style='border: 1px solid "+border_color+";'><div class='highlight_pick_background' style='background-image: url("+bild+");'><div class='highlight_pick_head'>"+spieler_nachname+"</div><div class='highlight_pick_foot'>"+team+"</div></div></div>";
		    		elements[k].insertAdjacentHTML("afterbegin", html_script);
			}

	    	$(".highlight_pick_wrap").css({"position": "relative", "width": "100px", "height": "100px", "background-color": "white"}); 
			$(".highlight_pick_background").css({"height": "100%", "width": "100%", "position": "absolute", "top": "0", "left": "0", "background-repeat": "no-repeat", "background-size": "100%", "text-align": "center"});

			$(".highlight_pick_head").css({"padding-left":"1px", "width":"100%", "background-color": "rgba(51, 51, 51, 0.9)", "position": "absolute", "top":"0", "left": "0", "color": "white", "text-overflow": "ellipsis",  "overflow": "hidden", "white-space": "nowrap", "font-size": "10px"}); 
			$(".highlight_pick_foot").css({"padding-left":"1px", "width":"100%", "background-color": "rgba(51, 51, 51, 0.9)", "position": "absolute", "bottom":"0", "left":"0", "color": "white", "text-overflow": "ellipsis",  "overflow": "hidden", "white-space": "nowrap", "font-size": "10px"}); 
		} 
	};
};


// Server Side Event: Markiere alle User die Ready sind
if(typeof(EventSource) !== "undefined") {
	  var source = new EventSource("sse/d_sse_players.php");
	  source.onmessage = function(event) {
	  	
		var stringResponse = event.data;
		console.log(stringResponse);
	  	var jdata = JSON.parse(stringResponse);
	  	console.log(jdata);

	  	var array_length = Object.keys(jdata).length;
		$('.draft_user').css('background-color', 'red');
		console.log(array_length);

		for (var key in jdata) {
			var username_db = "user_" + jdata[key];
			console.log(username_db);
			
			var elem = document.getElementById(username_db);
  			if(typeof elem !== 'undefined' && elem !== null) {
    			elem.style.backgroundColor = 'green';
  			} 
  		}
	  }
	 
	} else {
	  document.getElementById("top_flow_band").innerHTML = "Sorry, your browser does not support server-sent events...";
	}

// Server Side Event: Count Down Timer
if(typeof(EventSource) !== "undefined") {
  var source = new EventSource("sse/demo_sse.php");
  source.onmessage = function(event) {
  	
  	var jdata = JSON.parse(event.data);

  	if (jdata.draft_status == 'running'){

	  	var t1 = new Date(jdata.expire_ts); //new Date(event.data);
	  	var t2 = new Date(jdata.server_time);

	  	var dif = t2.getTime() - t1.getTime();
		var dif = Math.abs(dif) / 1000

		// calculate (and subtract) whole minutes
		var minutes = Math.floor(dif / 60) % 60;
		dif -= minutes * 60;

		// what's left is seconds
		var seconds = dif % 60

	    document.getElementById("countdown").innerHTML = minutes + ":" + seconds;

	    var on_the_clock_pick = jdata.on_the_clock_pick;
	    var on_the_clock_team = jdata.on_the_clock_team;
	    var on_the_clock_id = jdata.on_the_clock_id

	    document.getElementById("on_the_clock").innerHTML =  "Pick " + on_the_clock_pick + "<br>" + on_the_clock_team;

	    if (on_the_clock_id == php_session_user_id){
	    	$("#on_the_clock").addClass("blink_border");
			$("#countdown").addClass("blink_border");
		} else {
	    	$("#on_the_clock").removeClass("blink_border");
			$("#countdown").removeClass("blink_border");			
		}

	} else if (jdata.draft_status == 'open') {
	    document.getElementById("on_the_clock").innerHTML = "Draft 2020";		
	} else {
	    document.getElementById("on_the_clock").innerHTML = "No Draft Status found";	
	}

  };
} else {
  document.getElementById("countdown").innerHTML = "Sorry, your browser does not support server-sent events...";
}

// Click Funktion: Spieler markieren sich als ready
$(document).ready(function() {

	$('.draft_user').click(function(){
		var id = $(this).attr('id');
		
		request = $.ajax({
						type: "POST",
						url: "php/draft_update_ready_player.php",
						data: ({ ready_player: id })
				    });	
			//Wenn das php-Script erfolgreich durchläuft schicke dessen Ergebnis in die Seiten-Div
			request.done(function (response, textStatus, jqXHR){
						prevAjaxReturned = true;
					});
			//Wenn nicht, schreibe den Error in die Konsole
			request.fail(function (jqXHR, textStatus, errorThrown){
				        // Log the error to the console
				        console.error(
				            "The following error occurred: "+  textStatus, errorThrown
				        );
				    });	
	});
});


$(document).delegate('div.players_tr', 'click', function() {
    	//Bei Klick suche die erste Tabellenspalte (das ist hier die Spieler-ID) und speichere sie in die Variable player_id
   		var to_draft_player_id=$(this).closest('div').children('div:first').text();

   		//Schicke die Variable per AJAX an das php-Script
		request = $.ajax({
					type: "GET",
					url: "php/draft_view_player.php?",
					data: ({ to_draft_player_id: to_draft_player_id }),
			    });	
		//Wenn das php-Script erfolgreich durchläuft schicke dessen Ergebnis in die Seiten-Div
		request.done(function (response, textStatus, jqXHR){
					$(".main").load("https://fantasy-bundesliga.de/php/draft_view_player.php?to_draft_player_id="+to_draft_player_id);
					prevAjaxReturned = true;

				});
		//Wenn nicht, schreibe den Error in die Konsole
		request.fail(function (jqXHR, textStatus, errorThrown){
			        console.error(
			            "The following error occurred: "+  textStatus, errorThrown
			        );
			    });
    });
//});

$(document).ready(function() {
	//Macht alle Tabellenreihen der Tabellen der Klasse 'Kader' klickbar
    $('.dropdown_fantasy_team').click(function() {
    	//Bei Klick suche die erste Tabellenspalte (das ist hier die Spieler-ID) und speichere sie in die Variable player_id
   		var dropdown_player_id=$(this).attr("data-user-id");
   		var dropdown_chosen_team = $(this).text();
   		console.log(dropdown_player_id);
   		console.log(dropdown_chosen_team);
		//document.getElementById("draft_by_team").innerHTML = dropdown_player_id;
   		
   		//Schicke die Variable per AJAX an das php-Script 		
   		request = $.ajax({
					type: "GET",
					url: "php/draft_view_team.php?",
					data: ({ player_id: dropdown_player_id }),
			    });	
		//Wenn das php-Script erfolgreich durchläuft schicke dessen Ergebnis in die Seiten-Div
		request.done(function (response, textStatus, jqXHR){
			        //console.log(player_id)
					$("#draft_by_team").load("https://fantasy-bundesliga.de/php/draft_view_team.php?player_id="+dropdown_player_id);
					document.getElementById("view_team_button").innerHTML = dropdown_chosen_team;
					prevAjaxReturned = true;
			    });
		//Wenn nicht, schreibe den Error in die Konsole
		request.fail(function (jqXHR, textStatus, errorThrown){
			        // Log the error to the console
			        console.error(
			            "The following error occurred: "+  textStatus, errorThrown
			        );
			    });
		
    });
});

$(document).ready(function(){
	$(".main").load("https://fantasy-bundesliga.de/php/draft_grid.php");

});

$(document).delegate('.back_to_grid_button', 'click', function() {
	$(".main").load("https://fantasy-bundesliga.de/php/draft_grid.php");
});

$(document).ready(function() {
	//Macht alle Tabellenreihen der Tabellen der Klasse 'Kader' klickbar
    $('.filter_button').click(function() {
    	//Bei Klick suche die erste Tabellenspalte (das ist hier die Spieler-ID) und speichere sie in die Variable player_id
 		var filter_to_change_name = $(this).attr('id');
 		var filter_to_change_value = $(this).attr("data-active");

 		console.log(filter_to_change_name); 
 		console.log(filter_to_change_value);
 		
 		if (filter_to_change_name == "neuzugang_filter" || filter_to_change_name == "drafted_filter"){
 			//console.log("Ja, Schleife 1")
 			if (filter_to_change_value == 0){
	 			//console.log("Wert ist 0")
 				$(this).attr('data-active', '1');
 			} else if (filter_to_change_value == 1) {
	 			//console.log("Wert ist 1")
 				$(this).attr('data-active', '2');
 			} else if (filter_to_change_value == 2) {
	 			//console.log("Wert ist 2")
 				$(this).attr('data-active', '0');
 			}
 		} else if (filter_to_change_name == 'sum_avg_sort') {
 			if (filter_to_change_value == 1){
	 			//console.log("Wert ist 0")
 				$(this).attr('data-active', '0');
 			} else if (filter_to_change_value == 0) {
	 			//console.log("Wert ist 1")
 				$(this).attr('data-active', '1');
 			} 
 		} else if (filter_to_change_name == "st_filter" || filter_to_change_name == "mf_filter" || filter_to_change_name == "aw_filter" || filter_to_change_name == "tw_filter") {
 			if (filter_to_change_value == 0){
 				$(this).attr('data-active', '1');
 			} else if (filter_to_change_value == 1) {
 				$(this).attr('data-active', '0');
 			}
 		};

 		var neuzugang_filter=$("#neuzugang_filter").attr("data-active");
   		var drafted_filter=$("#drafted_filter").attr("data-active");
   		var st_filter=$("#st_filter").attr("data-active");		
   		var mf_filter=$("#mf_filter").attr("data-active");		
   		var aw_filter=$("#aw_filter").attr("data-active");		
   		var tw_filter=$("#tw_filter").attr("data-active");		
   		var sum_avg_sort=$("#sum_avg_sort").attr("data-active");		
		
		//document.getElementById("draft_by_team").innerHTML = dropdown_player_id;
   		//Schicke die Variable per AJAX an das php-Script
 		
   		request = $.ajax({
					type: "GET",
					url: "php/draft_filter_players.php?",
					data: ({ 	st_filter: st_filter,
								mf_filter: mf_filter,
								aw_filter: aw_filter,
								tw_filter: tw_filter,
								neuzugang_filter: neuzugang_filter,
								drafted_filter: drafted_filter,
								sum_avg_sort: sum_avg_sort
					 		}),
			    	});	
		//Wenn das php-Script erfolgreich durchläuft schicke dessen Ergebnis in die Seiten-Div
		request.done(function (response, textStatus, jqXHR){
			        //console.log("Hooray, it worked!");
			        //console.log(player_id)
					$("#selectable_players_list").load("https://fantasy-bundesliga.de/php/draft_filter_players.php?st_filter=" + st_filter + "&mf_filter=" + mf_filter + "&aw_filter=" + aw_filter + "&tw_filter=" + tw_filter + "&neuzugang_filter=" + neuzugang_filter + "&drafted_filter=" + drafted_filter + "&sum_avg_sort=" + sum_avg_sort);

					var filter_array = ["#st_filter", "#mf_filter", "#aw_filter", "#tw_filter", "#neuzugang_filter", "#drafted_filter"];
					filter_array.forEach(function(entry) {

						var change_color_entry = $(entry).attr("data-active");
						if (change_color_entry == 0){
							$(entry).css('background-color', 'red');
						} else if (change_color_entry == 1) {
							$(entry).css('background-color', 'green');
						} else if (change_color_entry == 2) {
							$(entry).css('background-color', 'lightgray');
						}
					});

					sort_status = $("#sum_avg_sort").attr("data-active");
					if (sort_status==0){
						$("#sum_avg_sort").text("AVG 🠗");
					} else if (sort_status==1){
						$("#sum_avg_sort").text("SUM 🠗");
					}
					

					prevAjaxReturned = true;
			    });
		//Wenn nicht, schreibe den Error in die Konsole
		request.fail(function (jqXHR, textStatus, errorThrown){
			        // Log the error to the console
			        console.error(
			            "The following error occurred: "+  textStatus, errorThrown
			        );
			    });
		
    });
});


/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
function team_dropdown() {
  document.getElementById("fantasy_teams_dropdown_content").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}


$(document).delegate('#draft_me_button', 'click', function() {

	var pick_in_ts = new Date().toLocaleString('de-DE', { timeZone: 'CET' });
	var click_player_id = $(this).attr("data-playerid");
	//alert(click_player_id);

	request = $.ajax({
					type: "GET",
					url: "php/draft_anfrage.php?",
					data: ({ 	click_player_id: click_player_id,
								pick_in_ts: pick_in_ts
					 		}),
			    	});	
		//Wenn das php-Script erfolgreich durchläuft schicke dessen Ergebnis in die Seiten-Div
		request.done(function (response, textStatus, jqXHR){
			        //console.log("Hooray, it worked!");
			        //console.log(player_id)
			        alert(response);
					prevAjaxReturned = true;
			    });
		//Wenn nicht, schreibe den Error in die Konsole
		request.fail(function (jqXHR, textStatus, errorThrown){
			        // Log the error to the console
			        console.error(
			            "The following error occurred: "+  textStatus, errorThrown
			        );
			    });

});

window.setInterval(function(){
	request = $.ajax({
					type: "GET",
					url: "php/draft_trigger_autopick.php",
					data: ({}) 	
			    	});	
	//Wenn das php-Script erfolgreich durchläuft schicke dessen Ergebnis in die Seiten-Div
	request.done(function (response, textStatus, jqXHR){
			        //console.log("Hooray, it worked!");
			        //console.log(player_id)
			        //alert(response);
					prevAjaxReturned = true;
			    });
	//Wenn nicht, schreibe den Error in die Konsole
	request.fail(function (jqXHR, textStatus, errorThrown){
			        // Log the error to the console
			        console.error(
			            "The following error occurred: "+  textStatus, errorThrown
			        );
			    });
}, 9000);

</script>

</head>
<body>
<header><h1>FANTASY BUNDESLIGA</h1></header>
<!-- Navigations-Menu-->
	<div id = "hilfscontainer">
		<?php include("navigation.php"); ?>
	</div>

	<!-- Seiten-Inhalt-->
	<div id="wrapper" class="row">
	<div id="content_wrapper">
		<div class="top_band">
			<div id="on_the_clock">
			</div>

			<!-- Top Zeile zeigt entweder die "Ich bin da"-Zeile oder Draft-Reihenfolge -->
			<?php 
				include 'db.php';	
				$draft_status = mysqli_query($con, "SELECT draft_status FROM xa7580_db1.draft_meta WHERE league_id = 1" ) -> fetch_object() -> draft_status;
				echo "<div id='top_flow_band'>";

				if ($draft_status == 'open'){
					$unique_players = mysqli_query($con, "	SELECT user_id, username, teamname, rank FROM xa7580_db1.draft_order WHERE league_id = 1 order by rank" );

					while($row = mysqli_fetch_array($unique_players)) {
						echo "<div class='draft_user' id='user_".mb_convert_encoding($row['user_id'], 'UTF-8')."' >";
						echo "Pick " . $row['rank'] . "<br>"; 
						echo mb_convert_encoding($row['teamname'], 'UTF-8');
						echo "</div>";
					}

				} elseif ($draft_status != 'open'){

				}

				echo "</div>";

				?>

			<div id="countdown">
			</div>
		</div>

		<div class="content">

			<!-- Linke Spalte für draftable Players -->
			<div class="left_column">

				<div id="left_head">
					<div id="search_player_wrapper">
						<input id="search_player" type="text" placeholder="Suche Spieler oder Verein..">
					</div>

					<div id="player_filters">
						<div id="position_filters">
							<div id='st_filter' class='filter_button' data-active='1'>ST</div>
							<div id='mf_filter' class='filter_button' data-active='1'>MF</div>
							<div id='aw_filter' class='filter_button' data-active='1'>AW</div>
							<div id='tw_filter' class='filter_button' data-active='1'>TW</div>
						</div>
						<div id ="misc_filters">
							<div id='drafted_filter' class='filter_button' data-active='2'>DRFT</div>
							<div id='neuzugang_filter' class='filter_button' data-active='2'>NEW</div>
							<div id='sum_avg_sort' class='filter_button' data-active='0'>AVG 🠗</div>
						</div>
					</div>
				</div>

				<div id="selectable_players_list">
					<?php
						include 'db.php';				
						$player_list = mysqli_query($con, "
															SELECT 	base.id
																	, base.display_name
																	, base.lastname
																	, base.teamname_code
																	, base.team_logo
																	, base.position_short
																	, rk.rank_pos_ftsy
																	, rk.sum_ftsy as sum_fantasy_punkte
																	, rk.avg_ftsy as avg_fantasy_punkte
																	, case when base.pick is not null then 1 else 0 end as picked_flg

															FROM xa7580_db1.draft_player_base base

															LEFT JOIN player_ranking_2019 rk
																ON rk.player_id = base.id

															ORDER BY rk.avg_ftsy DESC
										;");

						while($row = mysqli_fetch_array($player_list)) {

							if ($row['picked_flg'] == 1){ 
								echo "<div class='players_tr picked'>";
							} else {
								echo "<div class='players_tr unpicked'>";
							}
									echo "<div class='players_td' style='display:none;'>";
										echo utf8_encode($row['id']);
									echo "</div>";

									echo "<div class='players_td' id='td_position_short'>";
										echo utf8_encode($row['position_short']);
									echo "</div>";

									echo "<div class='players_td' id='td_spieler_name'>";
										echo mb_convert_encoding($row['display_name'], 'UTF-8');
									echo "</div>";

									echo "<div class='players_td' id='td_verein_short'>";
										echo mb_convert_encoding($row['teamname_code'], 'UTF-8');
									echo "</div>";

									echo "<div class='players_td' id='td_pos_ranking'>";
										echo $row['position_short'] . " #" . $row['rank_pos_ftsy'];
									echo "</div>";

									echo "<div class='players_td' id='td_fantasy_points'>";
										echo "∅ " . $row['avg_fantasy_punkte'] . " P.";
									echo "</div>";

									echo "<div class='players_td' id='td_fantasy_points'>";
										echo $row['sum_fantasy_punkte'] . " P.";
									echo "</div>";

							echo "</div>";
						}
					?>	
				</div>
			</div>

			<!-- Main Frame für Draft Board und Spieler-Anzeige -->
			<div class="main">	
			</div>

			<!-- Rechte Spalte für Team-Ansichten -->
			<div class="right_column">
				<div id="right_head">
					<div class="dropdown">
	  					<button id="view_team_button" onclick="team_dropdown()" class="dropbtn">Wähle ein Team...</button>
	  					<div id="fantasy_teams_dropdown_content" class="dropdown-content">
						    <?php
							include 'db.php';				
							$dropdown_team_list = mysqli_query($con, "	SELECT user_id, teamname FROM xa7580_db1.draft_order WHERE league_id = 1 order by teamname asc" );

							while($row = mysqli_fetch_array($dropdown_team_list)) {
								echo "<div class='dropdown_fantasy_team' data-user-id='".$row['user_id']."'>".mb_convert_encoding($row['teamname'], 'UTF-8')."</div>";
							}
							?>
	 					 </div>
	 				</div>
				</div>

				<div id="draft_by_team">
				</div>
			</div>

		</div>
	</div>
	</div>						
</body>
</html>
