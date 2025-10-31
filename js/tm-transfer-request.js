// Initiate global variables 
var clicked_player1 = 0;                
var clicked_player2 = 0; 

// Save IDs of clicked players in clicked_player1 and display clicked player name
$(function(){ 		
    $('.check1').click(function() {
        if($(this).is(':checked')){
            var currentRow=$(this).closest("tr"); 
            clicked_player1 = currentRow.find("td:eq(0)").text();   
            clicked_player1_name = currentRow.find("td:eq(2)").text();          
            document.getElementById('selected_players').innerHTML = "Aufzunehmender Spieler: " + clicked_player1_name;   
        }     
    });
});

// Save IDs of clicked players in clicked_player2 and display clicked player name
$(function(){
    $('.check2').click(function() {
        if($(this).is(':checked')){
            var currentRow=$(this).closest("tr"); 
            clicked_player2=currentRow.find("td:eq(0)").text(); 
            clicked_player2_name = currentRow.find("td:eq(2)").text();          
            document.getElementById('dropped_players').innerHTML = "Abzugebender Spieler: " + clicked_player2_name;
        }     
    });
});

// Call php script to with transfer request
function tranfer_request() {	
    $.ajax({												
        type: "GET",
        url: "../php/jobs/execute-ftsy-transfer.php",
        traditional: true,
        data: ({clicked_player1: clicked_player1, clicked_player2: clicked_player2}),
        success: function(results) { alert(results); }
    });
}	