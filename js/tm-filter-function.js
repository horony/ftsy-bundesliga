// Hide and show specific players

$(document).ready(function() {
  $('.filter_button').click(function() {
   		
		var filter_to_change_name = $(this).attr('id');
	 	var filter_to_change_value = $(this).attr("data-active");
	 	var class_to_filter = '.' + $(this).attr("data-filter-value");
		var hiding_class = 'inactive_' + $(this).attr("data-filter-value");

		//console.log(filter_to_change_name); 
		//console.log(filter_to_change_value);
		//console.log(class_to_filter);
	 		
		if (filter_to_change_value == 0){
			$(this).attr('data-active', '1');
			$(this).css('background-color', 'green');
			$(class_to_filter).removeClass(hiding_class);
		} else if (filter_to_change_value == 1) {
			$(this).attr('data-active', '0');
			$(this).css('background-color', 'red');
			$(class_to_filter).addClass(hiding_class);
		}
  });
});

function hideUSR(){
	$('.filter_own_usr').addClass('inactive_usr');
}