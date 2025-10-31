/* Colors buttons and makes options clickable */

function clickable(obj) {
    $choice = $(obj).text();
    $(".button").css("color","#333333");
    $(obj).css("color", "#4caf50");

    if ($choice != 'SPIELER' && $choice != 'TOP-PERFORMANCES'){
        $('#player_position_nav').css("display", "none");
    } else {
        $('#player_position_nav').css("display", "inline-flex");    	   	
    }  
}

function clickable_pos(obj) {
    $(".position_button").css("color","#333333");
    $(obj).css("color", "#4caf50");      
}