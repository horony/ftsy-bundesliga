$(document).ready(function() {

	// User can click dropdown menu to view a users drafted players
	$('.dropdown_fantasy_team').click(function() {
		var dropdown_player_id=$(this).attr("data-user-id");
		var dropdown_chosen_team = $(this).text();

		request = $.ajax({
			type: "GET",
			url: "../php/draft-display-team.php?",
			data: ({ player_id: dropdown_player_id }),
		});	

		request.done(function (response, textStatus, jqXHR){
			$("#draft_by_team").load("../php/draft-display-team.php?player_id="+dropdown_player_id);
			document.getElementById("view_team_button").innerHTML = dropdown_chosen_team;
			prevAjaxReturned = true;
		});
		
		request.fail(function (jqXHR, textStatus, errorThrown){
			console.error("The following error occurred: "+  textStatus, errorThrown);
		});
		
	});
});
