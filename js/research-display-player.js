$(document).ready(function() {
  $(".spieler_spotlight").load("../php/display-player-profile.php?player_id="+geklickter_spieler);
});
      
// Load clicked players with all their stats into HTML div
$(document).ready(function() {

	// Make all table rows of class .kader clickable
  $('.kader tr').click(function() {
		
		// On click get player_id
		var player_id=$(this).closest('tr').children('td:first').text();

		// Call php-script with player_id to retrieve data from MySQL DB
 		request = $.ajax({
			type: "GET",
			url: "../php/display-player-profile.php?",
			data: ({ player_id: player_id }),
	  });	
		
		request.done(function (response, textStatus, jqXHR){
			$(".spieler_spotlight").load("../php/display-player-profile.php?player_id="+player_id);
			prevAjaxReturned = true;
		});

		request.fail(function (jqXHR, textStatus, errorThrown){
      console.error("The following error occurred: " + textStatus, errorThrown);
		});
  });
});