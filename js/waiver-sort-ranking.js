/* Make users waiver ranking sortable by drag and drop */

$(function() { 								
  $("#active_waivers").sortable({placeholder: "ui-state-highlight"});
  $("#active_waivers").disableSelection();
});

/* Submit final ranking to PHP script in order to save it the MySQL DB */
	
function save_waiver_ranking() {															
	// Get active ranking
	var postData = $("#active_waivers").sortable('serialize');				

	// POST to PHP script
  $.ajax({												
		type: "POST",
		url: "../php/jobs/save-user-waiver-ranking.php",
		dataType: "json",
		traditional: true,
		data: {list: postData},
		success: function () {
			window.location.reload(true);
		},
		error: function (xhr, status, e) {
			window.location.reload(true);
			console.log(xhr.responseText);
		}
	});
}