/* Colors buttons and makes options clickable */

function clickable(obj) {
  console.log("Klick auf clickable", $(obj).text());
  $choice = $(obj).text();
  $(".button").css("color","#333333");
  $(obj).css("color", "#4caf50");
}

function clickable_lvl1(obj) {
  $(".lvl1_button").css("color","#333333");
  $(obj).css("color", "#4caf50");      
}

function clickable_lvl2(obj) {
  $(".lvl2_button").css("color","#333333");
  $(obj).css("color", "#4caf50");      
}