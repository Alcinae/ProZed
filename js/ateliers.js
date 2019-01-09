$(document).ready(function() {
    $(".subsLink").on("click", function() {
        
        
        
        $.ajax({
            url: 'ateliers_api.php', //uses session too
            dataType: 'json',
            type: 'post',
            contentType: 'text/json',
            data: {
                id: $(this).attr("data-id"),
                token: $(this).attr("data-token")
                val: $(this).is(':checked');
            },
            success: function( data, textStatus, jQxhr ){
                $(this).prop('checked', data["val"]);
                console.log(data["val"]);
            },
            error: function( jqXhr, textStatus, errorThrown ){
                console.log( errorThrown );
            }
        });
    });
    
    
    $('#dataTable').DataTable({
        "language": DTLang
        
    });
});
