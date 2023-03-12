$(document).ready(function(){
	$(".main").load("https://fantasy-bundesliga.de/php/draft-display-grid.php");
});

$(document).delegate('.back_to_grid_button', 'click', function() {
	$(".main").load("https://fantasy-bundesliga.de/php/draft-display-grid.php");
});
