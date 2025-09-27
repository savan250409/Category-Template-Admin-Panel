$(document).ready(function () {
    $('select[name="society_id"]').on('change', function() {
        var society_id = $(this).val();
        $.ajax({
            url: '/get_wings_data/' + society_id,
            type: 'get',
            success: function(data) {
                $('select[name="wings_id"]').empty();
                $('select[name="wings_id"]').append('<option >select wings name</option>');
                $.each(data, function(index, obj){
                    $('select[name="wings_id"]').append('<option value="'+obj.id+'">'+obj.wings_name+'</option>');
                })
            },
            error: function(data) {
                console.log('Error:', data);
            }

        })
    })




});

$(document).ready(function () {
    $('select[name="society_id"]').on('change', function() {
        var society_id = $(this).val();
        $.ajax({
            url: '/get_floor_data/' + society_id,
            type: 'get',
            success: function(data) {
                $('select[name="floor_id"]').empty();
                $('select[name="floor_id"]').append('<option >select floor name</option>');
                $.each(data, function(index, obj){
                    $('select[name="floor_id"]').append('<option value="'+obj.id+'">'+obj.floor_name+'</option>');
                })
            },
            error: function(data) {
                console.log('Error:', data);
            }

        })
    })


});

$(document).ready(function () {
    $('select[name="society_id"]').on('change', function() {
        var society_id = $(this).val();
        $.ajax({
            url: '/get_block_data/' + society_id,
            type: 'get',
            success: function(data) {
                $('select[name="block_id"]').empty();
                $('select[name="block_id"]').append('<option >select block name</option>');
                $.each(data, function(index, obj){
                    $('select[name="block_id"]').append('<option value="'+obj.id+'">'+obj.block_name+'</option>');
                })
            },
            error: function(data) {
                console.log('Error:', data);
            }

        })
    })


});







