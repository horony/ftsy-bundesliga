// Makes user teams shown in home news ticker clickable, linking directly to the teams page

$(document).ready(function() {
	$('.news_team').on('click',function(){
	 	var click_team =  $(this).data("id");

		request = $.ajax({
			type: "GET",
			url: "mein_team.php?",
			data: ({ show_team: show_team })
	  });	
	
		request.done(function (response, textStatus, jqXHR){
			window.open("https://fantasy-bundesliga.de/mein_team.php?show_team="+show_team, "_self");
			prevAjaxReturned = true;
	  });

		request.fail(function (jqXHR, textStatus, errorThrown){
			console.error("The following error occurred: "+  textStatus, errorThrown);
		});			
	});
});

// Makes user teams shown in home standings clickable, linking directly to the teams page

$(document).ready(function() {
	$('#tabellen').on('click','a.news_team',function(){
		var click_team =  $(this).data("id");

		request = $.ajax({
			type: "GET",
			url: "mein_team.php?",
			data: ({ show_team: show_team })
		});	

		request.done(function (response, textStatus, jqXHR){
			window.open("https://fantasy-bundesliga.de/mein_team.php?show_team="+show_team, "_self");
			prevAjaxReturned = true;
		});
			
		request.fail(function (jqXHR, textStatus, errorThrown){
			console.error("The following error occurred: "+  textStatus, errorThrown);
		});		
	});
});

// Makes user teams shown in home Fantasy fixtures clickable, linking directly to the teams page

$(document).ready(function() {
  $('#boxscores').on('click','a.match_team',function(){
		var click_match =  $(this).data("id");

		request = $.ajax({
			type: "GET",
				url: "view_match.php?",
				data: ({ ID: click_match })
		});	

		request.done(function (response, textStatus, jqXHR){
			window.open("https://fantasy-bundesliga.de/view_match.php?ID="+click_match, "_self");
			prevAjaxReturned = true;
		});

		request.fail(function (jqXHR, textStatus, errorThrown){
			console.error("The following error occurred: "+  textStatus, errorThrown);
		});
	});
});

// Makes players shown in home news ticker clickable, linking directly to the research page

$(document).ready(function() {
  $('.news_player').on("click",function(){
	var click_player =  $(this).data("id");

	request = $.ajax({
		type: "GET",
		url: "spieler_datenbank.php?",
		data: ({ click_player: click_player })
	});	
	
	request.done(function (response, textStatus, jqXHR){
	  window.open("https://fantasy-bundesliga.de/research.php?click_player="+click_player);
		prevAjaxReturned = true;
  });
	
	request.fail(function (jqXHR, textStatus, errorThrown){
		console.error("The following error occurred: "+  textStatus, errorThrown);});
	});
});