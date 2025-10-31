// Show table

function showTable(str) {
    var xhttp;
    window.clicked_spieltag = $clicked_spieltag;
    var url_string = window.location.href;
    var url = new URL(url_string);
    var show_team = url.searchParams.get("show_team");

    if (str == "") {
        document.getElementById("aufstellung_wrapper").innerHTML = "";
        return;
    }

    xhttp = new XMLHttpRequest();

    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("aufstellung_wrapper").innerHTML = this.responseText;
        }
    };

    xhttp.open("GET", "../php/display-ftsy-team-in-table.php?q="+str+"&show_team="+show_team+"&tag="+$clicked_spieltag, true);
    xhttp.withCredentials = true;
    xhttp.send(null);
    window.active_view = 'Table';
}

function changeColorTable(){
  document.getElementById("button_show_tabelle").style.color = "#4caf50";
  document.getElementById("button_show_grafisch").style.color = "#333333";
}

// Show Graph
function showGraphic(str) {
    var xhttp;
    window.clicked_spieltag = $clicked_spieltag;
    var url_string = window.location.href;
    var url = new URL(url_string);
    var show_team = url.searchParams.get("show_team");

    if (str == "") {
        document.getElementById("aufstellung_wrapper").innerHTML = "";
        return;
    }

    xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {

            document.getElementById("aufstellung_wrapper").innerHTML = this.responseText;
            var max = 0;

            $('.player_card_item').each(function(i) {
                if(max < $(this).width()) {
                    max = $(this).width() + 20;
                }
            });

            $('.player_card_item').css('width', max);
        }
    };
  
    xhttp.open("GET", "../php/display-ftsy-team-in-graph.php?show_team="+show_team+"&tag="+$clicked_spieltag, true);
    xhttp.withCredentials = true;
    xhttp.send(null);
    window.active_view = 'Graph';
}

function changeColorGraphic(){
    document.getElementById("button_show_tabelle").style.color = "#333333";
    document.getElementById("button_show_grafisch").style.color = "#4caf50";
}