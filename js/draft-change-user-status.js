$(document).ready(function() {

	// Users can change their draft status by clicking on their team

	$('.draft_user').click(function(){
		var id = $(this).attr('id');

		request = $.ajax({
			type: "POST",
			url: "../php/jobs/draft-change-user-status.php",
			data: ({ ready_player: id })
		});	
		
		request.done(function (response, textStatus, jqXHR){
			prevAjaxReturned = true;
		});

		request.fail(function (jqXHR, textStatus, errorThrown){
			console.error("The following error occurred: "+  textStatus, errorThrown);
		});	
	});
});