window.setInterval(function(){

	request = $.ajax({
		type: "GET",
		url: 	"https://fantasy-bundesliga.de/php/jobs/draft-execute-autopick.php",
		data: ({}) 	
	});	

	request.done(function (response, textStatus, jqXHR){
		prevAjaxReturned = true;
	});
	
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.error("The following error occurred: "+  textStatus, errorThrown);
	});

}, 9000);