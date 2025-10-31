if(typeof(EventSource) !== "undefined") {

    var user_status_change = new EventSource("../php/sse/draft-sse-ready-users.php");
    
    user_status_change.onmessage = function(event) {

        var stringResponse = event.data;
        var jdata = JSON.parse(stringResponse);
        var array_length = Object.keys(jdata).length;

        // Default: Set background to ready because user is not ready
        $('.draft_user').css('background-color', 'red');

        // If user is ready: Set background to green
        for (var key in jdata) {
            var username_db = "user_" + jdata[key];
            var elem = document.getElementById(username_db);
            if(typeof elem !== 'undefined' && elem !== null) {
                elem.style.backgroundColor = 'green';
            }
        }
    }
} else {
    document.getElementById("top_flow_band").innerHTML = "Your browser does not support server-sent events.";
}