$(document).ready(function(){
  // Your code here
  $('.date').date_input();
});


function now() {
  var d = new Date();
  var y = d.getFullYear();
  var m = d.getMonth() + 1;
  if (m < 10) m = '0' + m;
  var day = d.getDate();
  if (day < 10) day = '0' + day;
  var t = d.toTimeString().substr(0, 8);
  return y+'-'+m+'-'+day+' '+t;
}


function input_current_timestamp(box, datatype) {
  var n = box.previousSibling;
  while (n.nodeType != 1) {
    n = n.previousSibling; // Firefox (and others) consider whitespace nodes.
  }
  if (box.checked) {
    n.old_value = n.value;
    n.value = (datatype == 'date') ? now().substr(0, 10) : now();
  }else{
    n.value = n.old_value;
  }
}


// confirmDelete()
function confirmDelete() {
  if ($('form textarea').val().toLowerCase().indexOf('delete') > -1) {
    return confirm("Dude, Looks like you're deleting stuff, are you Suuuuure?");
  }
  return true;
}

/* -- Log --------------------------------

[2010-05-10 18:09:16] Added confirmDelete()
[2009-01-03 13:16:42] Created.

*/
