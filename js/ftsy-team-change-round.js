$(document).ready(function(){

	$('div.nav_spieltag').click(function(){
		elements = document.getElementsByClassName('nav_spieltag');
		for (var i = 0; i < elements.length; i++) {
        	elements[i].style.color="#3f3333";
        	elements[i].style.fontWeight="400";
    	}
		this.style.color = '#4caf50';
		this.style.fontWeight = "600";
		$clicked_spieltag = $(this).text();

		const element = document.querySelector('#button_show_tabelle')
		const button_color = element.style.color

		if (button_color != 'rgb(76, 175, 80)'){
			showGraphic();
		} else {
			showTable();
		}
	});
	
});