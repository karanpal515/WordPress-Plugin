jQuery(function($) {
    //console.log("jQuery is loaded");

    $('#genre_filter').change(function(e) {
       // console.log('Genre filter changed'); 
        
        e.preventDefault();  
        
        var genre_id = $(this).val();
        console.log('Selected Genre ID:', genre_id);

        // AJAX request
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                action: 'filter_books_by_genre',
                genre_id: genre_id,
            },
            beforeSend: function() {
                // Show loader before the AJAX request
                $('#append_posttype').empty();
                $('.loader').show(); 
            },
            success: function(response) {
                console.log('AJAX Success:', response);
                // Display the filtered results
                $('#append_posttype').html(response);
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error: ', error);
            },
            complete: function() {
                $('.loader').hide();
            }
        });
    });
});
