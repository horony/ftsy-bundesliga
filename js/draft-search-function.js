$(document).ready(function(){
    $("#search_player").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".players_tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});	