// Define colors in standings table based on specific characters and color table rows
$(document).ready(function(){

    // Color up character etc.
    $("#spielstand td.updown:contains('▲')").css('color','darkgreen');
    $("#spielstand td.updown:contains('▼')").css('color','darkred');
    $("#spielstand td.updown:contains('-')").css('color','gray');

    // Color winning streak 
    $("#spielstand td.serie_color:contains('U')").each(function () {
        $(this).html($(this).html().replace(new RegExp("U", "g"), "<span style='color: orange;'>U</span>"));
    });

    $("#spielstand td.serie_color:contains('S')").each(function () {
        $(this).html($(this).html().replace(new RegExp("S", "g"), "<span style='color: darkgreen;'>S</span>"));
    });

    $("#spielstand td.serie_color:contains('N')").each(function () {
        $(this).html($(this).html().replace(new RegExp("N", "g"), "<span style='color: darkred;'>N</span>"));
    });
});