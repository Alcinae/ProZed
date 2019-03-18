     $(document.body).on('click', '.delete-link' ,function(e){
        e.preventDefault();
        
        $("#confirmModalLink").attr("href", $(this).attr("href"));
        $("#confirmModalValueData").text($(this).attr("data-modalHint"));
        $('#confirmModal').modal('show');
     });
