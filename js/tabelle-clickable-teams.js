// Make teams in tabelle clickable and redirect user to teams page

$(document).ready(function() {

	// Make whole table row clickable
	$('#spielstand tr').click(function() {
	    	
	  // Get table row element with teams user id
	  var show_team=$(this).closest('tr').children('td:nth-child(3)').text();
	  show_team=show_team.split('🥇').join(',').split('🏆').join(',').split('🏮').join(',').split(',');
	  show_team=show_team[0];

	  // Redirect user to team page
		request = $.ajax({
			type: "GET",
			url: "mein_team.php?",
			data: ({ show_team: show_team })
		});	
		
		// Success
		request.done(function (response, textStatus, jqXHR){
			window.open("https://fantasy-bundesliga.de/mein_team.php?show_team="+show_team, "_self");
			prevAjaxReturned = true;
    });
	
		// Failure
		request.fail(function (jqXHR, textStatus, errorThrown){
			console.error("The following error occurred: " + textStatus, errorThrown);
		});
			
	});
});