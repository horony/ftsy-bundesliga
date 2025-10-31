$(document).ready(function(){
    $(".main").load("../php/draft-display-grid.php");
});

$(document).delegate('.back_to_grid_button', 'click', function() {
    $(".main").load("../php/draft-display-grid.php");
});
