// Display current Fantasy fixtures

var request;

$(document).ready(function(){	
    $("select").change(function(event){
        event.preventDefault();
      if (request) { request.abort(); }
        var spieltag = $(this).val();             

        // Get current Fantasy round from MySQL DB
        request = $.ajax({
            type: "GET",
            url: "../php/display-current-fantasy-fixtures.php",
            data: ({ spieltag: spieltag }),
            dataType: "html",
        });	
        
        // On success insert returned HTML directly to avoid a second XHR (.load)
        request.done(function (response, textStatus, jqXHR){
            // response is the rendered HTML from display-current-fantasy-fixtures.php
            $("#spieltag_tabelle").html(response);
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