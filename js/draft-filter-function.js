$(document).ready(function() {
	
	$('.filter_button').click(function() {

		var filter_to_change_name = $(this).attr('id');
		var filter_to_change_value = $(this).attr("data-active");

		if (filter_to_change_name == "neuzugang_filter" || filter_to_change_name == "drafted_filter"){

			if (filter_to_change_value == 0){
				$(this).attr('data-active', '1');
			} else if (filter_to_change_value == 1) {
				$(this).attr('data-active', '2');
			} else if (filter_to_change_value == 2) {
				$(this).attr('data-active', '0');
			}
		
		} else if (filter_to_change_name == 'sum_avg_sort') {
			
			if (filter_to_change_value == 1){
				$(this).attr('data-active', '0');
			} else if (filter_to_change_value == 0) {
				$(this).attr('data-active', '1');
			} 
		
		} else if (filter_to_change_name == "st_filter" || filter_to_change_name == "mf_filter" || filter_to_change_name == "aw_filter" || filter_to_change_name == "tw_filter") {
		
			if (filter_to_change_value == 0){
				$(this).attr('data-active', '1');
			} else if (filter_to_change_value == 1) {
				$(this).attr('data-active', '0');
			}
		};

		var neuzugang_filter=$("#neuzugang_filter").attr("data-active");
		var drafted_filter=$("#drafted_filter").attr("data-active");
		var st_filter=$("#st_filter").attr("data-active");		
		var mf_filter=$("#mf_filter").attr("data-active");		
		var aw_filter=$("#aw_filter").attr("data-active");		
		var tw_filter=$("#tw_filter").attr("data-active");		
		var sum_avg_sort=$("#sum_avg_sort").attr("data-active");		

		request = $.ajax({
			type: "GET",
			url: "../php/draft-filter-players.php?",
			data: ({ 	
				st_filter: st_filter,
				mf_filter: mf_filter,
				aw_filter: aw_filter,
				tw_filter: tw_filter,
				neuzugang_filter: neuzugang_filter,
				drafted_filter: drafted_filter,
				sum_avg_sort: sum_avg_sort
			}),
		});

		request.done(function (response, textStatus, jqXHR){
			$("#selectable_players_list").load("https://fantasy-bundesliga.de/php/draft-filter-players.php?st_filter=" + st_filter + "&mf_filter=" + mf_filter + "&aw_filter=" + aw_filter + "&tw_filter=" + tw_filter + "&neuzugang_filter=" + neuzugang_filter + "&drafted_filter=" + drafted_filter + "&sum_avg_sort=" + sum_avg_sort);
			var filter_array = ["#st_filter", "#mf_filter", "#aw_filter", "#tw_filter", "#neuzugang_filter", "#drafted_filter"];
			
			filter_array.forEach(function(entry) {
				var change_color_entry = $(entry).attr("data-active");
				if (change_color_entry == 0){
					$(entry).css('background-color', 'red');
				} else if (change_color_entry == 1) {
					$(entry).css('background-color', 'green');
				} else if (change_color_entry == 2) {
					$(entry).css('background-color', 'lightgray');
				}
			});
			
			sort_status = $("#sum_avg_sort").attr("data-active");
			if (sort_status==0){
				$("#sum_avg_sort").text("AVG 🠗");
			} else if (sort_status==1){
				$("#sum_avg_sort").text("SUM 🠗");
			}

			prevAjaxReturned = true;
		});

		request.fail(function (jqXHR, textStatus, errorThrown){
			console.error("The following error occurred: "+  textStatus, errorThrown);
		});

	});
});