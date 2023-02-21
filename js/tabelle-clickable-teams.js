// Make teams in tabelle clickable and redirect user to teams page

$(document).ready(function() {

	// Make whole table row clickable
	$('#spielstand tr').click(function() {
	    	
	  // Get table row element with teams user id
	  var click_team=$(this).closest('tr').children('td:nth-child(3)').text();

	  // Redirect user to team page
		request = $.ajax({
			type: "GET",
			url: "view_team.php?",
			data: ({ click_team: click_team })
		});	
		
		// Success
		request.done(function (response, textStatus, jqXHR){
			window.open("https://fantasy-bundesliga.de/view_team.php?click_team="+click_team);
			prevAjaxReturned = true;
    });
	
		// Failure
		request.fail(function (jqXHR, textStatus, errorThrown){
			console.error("The following error occurred: " + textStatus, errorThrown);
		});
			
	});
});