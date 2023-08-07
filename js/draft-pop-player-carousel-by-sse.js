if(typeof(EventSource) !== "undefined") {

	var pick_event = new EventSource("../php/sse/draft-sse-recent-picks.php");

	pick_event.onmessage = function(event) {
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

			// Create carousel structure
			for (var i = 0; i < array_length; i++){
				div_element = document.createElement("div");
				id_name = 'highlight_pick_no_' + i.toString();
				div_element.id = id_name;
				div_element.className = "highlight_pick";
				document.getElementById("top_flow_band").appendChild(div_element);
				array_highlight_pick_ids.push(id_name);
			} 

			var elements = document.getElementsByClassName("highlight_pick"); 
			
			// Populate carousel
			for (var k = 0; k < elements.length; k++) { 
				// Get player info
				spieler_nachname = decodeURIComponent(jdata_picked_players[k][3]);
				if (spieler_nachname === ''){
					spieler_nachname = 'Pick ' + jdata_picked_players[k][1];
				}
				team = jdata_picked_players[k][2];
				var bild = jdata_picked_players[k][4];

				// Highlights users own picks
				if (php_session_user_id == jdata_picked_players[k][5]){
					border_color = 'red';
				} else {
					border_color = 'black';
				}

				// Generate HTML output
				var html_script = "<div class='highlight_pick_wrap' style='border: 1px solid "+border_color+";'><div class='highlight_pick_background' style='background-image: url("+bild+");'><div class='highlight_pick_head'>"+spieler_nachname+"</div><div class='highlight_pick_foot'>"+team+"</div></div></div>";
				elements[k].insertAdjacentHTML("afterbegin", html_script);
			}

			// Define css
			$(".highlight_pick_wrap").css({"position": "relative", "width": "100px", "height": "100px", "background-color": "white"}); 
			$(".highlight_pick_background").css({"height": "100%", "width": "100%", "position": "absolute", "top": "0", "left": "0", "background-repeat": "no-repeat", "background-size": "100%", "text-align": "center"});

			$(".highlight_pick_head").css({"padding-left":"1px", "width":"100%", "background-color": "rgba(51, 51, 51, 0.9)", "position": "absolute", "top":"0", "left": "0", "color": "white", "text-overflow": "ellipsis",  "overflow": "hidden", "white-space": "nowrap", "font-size": "10px"}); 
			$(".highlight_pick_foot").css({"padding-left":"1px", "width":"100%", "background-color": "rgba(51, 51, 51, 0.9)", "position": "absolute", "bottom":"0", "left":"0", "color": "white", "text-overflow": "ellipsis",  "overflow": "hidden", "white-space": "nowrap", "font-size": "10px"}); 
		} 
	};
};
