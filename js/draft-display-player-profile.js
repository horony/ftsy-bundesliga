$(document).delegate('div.players_tr', 'click', function() {
    
    var to_draft_player_id=$(this).closest('div').children('div:first').text();

    request = $.ajax({
        type: "GET",
        url: "../php/draft-display-player-profile.php?",
        data: ({ to_draft_player_id: to_draft_player_id }),
    });	

    request.done(function (response, textStatus, jqXHR){
        // Display player profile through PHP into the main section of the page
        $(".main").load("../php/draft-display-player-profile.php?to_draft_player_id="+to_draft_player_id);
        prevAjaxReturned = true;
    });
    
    request.fail(function (jqXHR, textStatus, errorThrown){
        console.error("The following error occurred: "+  textStatus, errorThrown);
    });
});