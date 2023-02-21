// Display current Fantasy fixtures
$(document).ready(function(){
	
	$("select").change(function(event){
	
		event.preventDefault();
	  if (request) { request.abort(); }
		var spieltag = $(this).val();             

		// Get current Fantasy round from MySQL DB
		request = $.ajax({
			type: "GET",
			url: "../php/display-current-fantasy-fixtures.php?",
			data: ({ spieltag: spieltag }),
	  });	
		
		// On success print current fixtures into HTML page
		request.done(function (response, textStatus, jqXHR){
			$("#spieltag_tabelle").load("https://fantasy-bundesliga.de/php/display-current-fantasy-fixtures.php?spieltag="+spieltag);
			prevAjaxReturned = true;
		});

		// On error
		request.fail(function (jqXHR, textStatus, errorThrown){
			console.error("The following error occurred: " + textStatus, errorThrown);
		});
	});
});

// Define default value for current round on page load
$(function(){

	$.ajax({   
    type: "GET",
    url: "../php/get-current-fantasy-round.php",             
    dataType: "text",                 
    success: function(response){                    
      var spieltag = response
      $("select").val(spieltag).trigger('change');
    }
	});

});