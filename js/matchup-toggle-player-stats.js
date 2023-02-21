// Make player rows clickable to show and hide player stats

$(document).ready(function() {
	$('.summary1').click(function(){
	  $(this).nextUntil('tr.summary1').css('display', function(i,v){
	    return this.style.display === 'table-row' ? 'none' : 'table-row';
	  });
	});
});

$(document).ready(function() {
	$('.summary2').click(function(){
	  $(this).nextUntil('tr.summary2').css('display', function(i,v){
	  	return this.style.display === 'table-row' ? 'none' : 'table-row';
	  });
	});
});

$(document).ready(function() {
	$('.summary3').click(function(){
	  $(this).nextUntil('tr.summary3').css('display', function(i,v){
	    return this.style.display === 'table-row' ? 'none' : 'table-row';
	  });
	});
});

$(document).ready(function() {
	$('.summary4').click(function(){
	  $(this).nextUntil('tr.summary4').css('display', function(i,v){
	    return this.style.display === 'table-row' ? 'none' : 'table-row';
	  });
	});
});