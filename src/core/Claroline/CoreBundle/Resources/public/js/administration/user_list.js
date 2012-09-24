(function () {
    $('html, body').animate({scrollTop: 0}, 0);
    $('#loading').hide();

    var loading = false;
    addContent();

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false){
            loading = true;
            $('#loading').show();
            var route = Routing.generate('claro_admin_paginated_user_list', {
                'offset' : $('.row-user').length,
                'format': 'html'
            });
            ClaroUtils.sendRequest(route, function(users){
                $('#user-table-body').append($(users));
                loading = false;
                $('#loading').hide();
            })
        }
    });

    $('.delete-users-button').click(function(){
        $('#validation-box').modal('show');
        $('#validation-box-body').html('removing '+ $('.chk-user:checked').length +' user(s)');
    });

    $('#modal-valid-button').click(function(){
        var parameters = {};
        var i = 0;
        $('.chk-user:checked').each(function(index, element){
            parameters[i] = element.value;
            i++;
        });

        var route = Routing.generate('claro_admin_multidelete_user', parameters);
        ClaroUtils.sendRequest(
            route,
            function(){
                $('.chk-user:checked').each(function(index, element){
                     $(element).parent().parent().remove();
                });
                $('#validation-box').modal('hide');
                $('#validation-box-body').empty();
            },
            undefined,
            'DELETE'
        );
    });

    $('#modal-cancel-button').click(function(){
        $('#validation-box').modal('hide');
        $('#validation-box-body').empty();
    });

    function addContent(){
        if($(window).height() >= $(document).height()){
            var route = Routing.generate('claro_admin_paginated_user_list', {
                'offset' : $('.row-user').length,
                'format': 'html'
            });

            ClaroUtils.sendRequest(route, function(users){
                $('#user-table-body').append($(users));
                addContent();
            })
        }
    }
})();