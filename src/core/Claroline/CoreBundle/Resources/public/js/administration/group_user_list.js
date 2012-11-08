//window.onload(function(){alert("loading...")});
(function () {
    var loading = false;
    var stop = false;
    var mode = 0; //0 = standard || 1 = search
    var groupId = document.getElementById('twig-attributes').getAttribute('data-group-id');

    $('html, body').animate({
        scrollTop: 0
    }, 0);
    $('#loading').hide();

    var standardRoute = function(){
        return Routing.generate('claro_admin_paginated_group_user_list', {
            'offset' : $('.row-user').length,
            'groupId': groupId
        });
    }

    var searchRoute = function(){
        return Routing.generate('claro_admin_paginated_search_group_user_list', {
            'offset' : $('.row-user').length,
            'groupId': groupId,
            'search':  document.getElementById('search-user-txt').value
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
        $('#validation-box-body').html(Twig.render(remove_user_confirm, { 'nbUsers' :$('.chk-user:checked').length} ));
    });

    $('#modal-cancel-button').click(function(){
        $('#validation-box').modal('hide');
        $('#validation-box-body').empty();
    });

    $('#modal-valid-button').click(function(){
        var parameters = {};
        var array = new Array()
        var i = 0;
        $('.chk-user:checked').each(function(index, element){
            array[i] = element.value;
            i++;
        });
        parameters.userId = array;
        var route = Routing.generate('claro_admin_multidelete_user_from_group', {'groupId': groupId});
        route+='?'+$.param(parameters);
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


    function lazyloadUsers(route){
        loading = true;
        $('#loading').show();
        ClaroUtils.sendRequest(
            route(),
            function(users){
                $('#user-table-body').append(Twig.render(user_list_short, {
                    'users': users
                }));
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