// Make fixtures on Spieltag-page clickable

function viewBuliFixture(input) {
	
	// Generate match id from input
	var input_id = String(input);
	var ID = input_id;

	// Send ID to ajax
	request = $.ajax({
		type: "GET",
		url: "view_match_buli?",
		data: ({ ID: ID })
	});	
	
	// On success link user to match page
	request.done(function (response, textStatus, jqXHR){
		window.open("view_match_buli.php?ID="+ID);
		prevAjaxReturned = true;
	});

	// On error fail
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.error("The following error occurred: " + textStatus, errorThrown);
	});
}