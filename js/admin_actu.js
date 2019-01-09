var chkBox_expire = document.getElementById("chkBox_expire");
var box = document.getElementById("dateBox");

function updateBox() {
    if(chkBox_expire.checked)
    {
        box.style.visibility = "visible";
    }else{
        box.style.visibility = "hidden"
    }
}


chkBox_expire.onchange = updateBox;
$(document).ready(function() {
    updateBox();
    $('#dataTable').DataTable({
            "language": DTLang
      
    });
});

/*
$(document).ready(function() {
  updateBox();
  $('#dataTable').DataTable({
    lengthChange: false,
        ajax: "ajax_actu.php",
        columns: [
            { data: "message" },
            { data: "expiration" },
            { data: "type" }
        ],
        select: true
    } );
});
*/
