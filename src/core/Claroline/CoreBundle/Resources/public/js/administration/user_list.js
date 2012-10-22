(function () {
    var loading = false;
    var stop = false;
    var mode = 0; //0 = standard || 1 = search

    $('html, body').animate({scrollTop: 0}, 0);
    $('#loading').hide();

    var standardRoute = function(){
        return Routing.generate('claro_admin_paginated_user_list', {
            'offset' : $('.row-user').length,
            'format': 'html'
        });
    }

    var searchRoute = function(){
        return Routing.generate('claro_admin_paginated_search_user_list', {
            'format': 'html',
            'offset': $('.row-group').length,
            'search': document.getElementById('search-user-txt').value
        })
    }

    lazyloadUsers(standardRoute);

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false && stop === false){
            if(mode == 0){
                lazyloadUsers(standardRoute);
            } else {
                lazyloadUsers(searchRoute);
            }
        }
    });

   $('#search-user-button').click(function(){
        $('#user-table-body').empty();
        stop = false;
        if (document.getElementById('search-user-txt').value != ''){
            mode = 1;
            lazyloadUsers(searchRoute);
        } else {
            mode = 0;
            lazyloadUsers(standardRoute);
        }
    });

    $('.delete-users-button').click(function(){
        $('#validation-box').modal('show');
        $('#validation-box-body').html('removing '+ $('.chk-user:checked').length +' user(s)');
    });

    $('#modal-valid-button').click(function(){
        var parameters = {
        }
        var i = 0;
        var array = new Array()
        $('.chk-user:checked').each(function(index, element){
            array[i] = element.value;
            i++;
        });
        parameters.id = array;

        var route = Routing.generate('claro_admin_multidelete_user');
        route+= '?'+$.param(parameters);
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

    function lazyloadUsers(route){
        loading = true;
        $('#loading').show();
        ClaroUtils.sendRequest(
            route(),
            function(users){
                $('#user-table-body').append(users);
                loading = false;
                $('#loading').hide();
                if (users.length == 0) {
                    stop = true;
                }
            },
            function(){
                if($(window).height() >= $(document).height() && stop == false){
                    lazyloadUsers(route)
                }
            }
        )
    }
})();