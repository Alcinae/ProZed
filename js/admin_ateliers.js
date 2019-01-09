

$(document).ready(function() {
    
  document.getElementById("desc").value = "";
  
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
