// Make player rows clickable to show and hide player stats
$(document).ready(function() {
    $('.summary1').click(function(){
        $(this).nextUntil('tr.summary1').css('display', function(i,v){
            return this.style.display === 'table-row' ? 'none' : 'table-row';
        });
    });
});

// Make roster rows clickable to show and hide player stats
$(document).ready(function() {
    $('.roster-row').click(function(){
        $(this).nextUntil('tr.roster-row').css('display', function(i,v){
            return this.style.display === 'table-row' ? 'none' : 'table-row';
        });
    });
});