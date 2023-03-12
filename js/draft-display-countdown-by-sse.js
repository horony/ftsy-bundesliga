if(typeof(EventSource) !== "undefined") {

	var source = new EventSource("../php/sse/draft-sse-active-pick.php");
	
	source.onmessage = function(event) {

		var jdata = JSON.parse(event.data);

		if (jdata.draft_status == 'running'){

			var t1 = new Date(jdata.expire_ts); 
			var t2 = new Date(jdata.server_time);
			var dif = t2.getTime() - t1.getTime();
			var dif = Math.abs(dif) / 1000

			// calculate (and subtract) whole minutes
			var minutes = Math.floor(dif / 60) % 60;
			dif -= minutes * 60;
			var seconds = dif % 60

			// Display countdown
			document.getElementById("countdown").innerHTML = minutes + ":" + seconds;

			// Display team that is on the clock
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
			
			document.getElementById("on_the_clock").innerHTML = "Draft 23/24";		

		} else {
			
			document.getElementById("on_the_clock").innerHTML = "No Draft Status found";	
		}
	};
} else {
	document.getElementById("countdown").innerHTML = "Your browser does not support server-sent events...";
}