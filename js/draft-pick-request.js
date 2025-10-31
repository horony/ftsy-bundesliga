$(document).delegate('#draft_me_button', 'click', function() {

    var pick_in_ts = new Date().toLocaleString('de-DE', { timeZone: 'CET' });
    var click_player_id = $(this).attr("data-playerid");

    request = $.ajax({
        type: "GET",
        url: "../php/jobs/draft-execute-pick.php?",
        data: ({
            click_player_id: click_player_id,
            pick_in_ts: pick_in_ts
        }),
    });

    request.done(function (response, textStatus, jqXHR){
        alert(response);
        prevAjaxReturned = true;
    });
    
    request.fail(function (jqXHR, textStatus, errorThrown){
        console.error("The following error occurred: "+  textStatus, errorThrown);
    });

});