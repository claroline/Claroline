(function(){
    $('html, body').animate({
        scrollTop: 0
    }, 0);

    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var loading = false;
    var stop = false;
    var mode = 0; //0 = standard || 1 = search

    $('.delete-users-button').attr('disabled', 'disabled');

    $('.chk-delete-user').live('change', function(){
        if ($('.chk-delete-user:checked').length){
           $('.delete-users-button').removeAttr('disabled');
        } else {
           $('.delete-users-button').attr('disabled', 'disabled');
        }
    })

    var standardRoute = function(){
        return Routing.generate('claro_workspace_registered_users_paginated', {
                'workspaceId':twigWorkspaceId,
                'offset': $('.row-user').length
            });
    }

    var searchRoute = function(){
        return Routing.generate('claro_workspace_search_registered_users', {
                'search': document.getElementById('search-user-txt').value,
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-user').length
            });
    }

    lazyloadUsers(standardRoute);
    $('#user-loading').hide();

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false && stop === false){
            if(mode == 0){
                lazyloadUsers(standardRoute);
            } else {
                lazyloadUsers(searchRoute);
            }
        }
    });

    $('.button-parameters-user').live('click', function(e){
        var route = Routing.generate(
            'claro_workspace_tools_show_user_parameters',
            {'userId': $(this).attr('data-user-id'), 'workspaceId': twigWorkspaceId}
        );

        window.location.href = route;
    })

    $('#search-user-button').click(function(){
        $('.chk-delete-user').remove();
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

    $('#delete-user-button').click(function(){
        $('#validation-box').modal('show');
          $('#validation-box-body').html(Twig.render(remove_user_confirm, {'nbUsers' :$('.chk-delete-user:checked').length }));
    });

    $('#modal-valid-button').click(function(){
        var parameters = {};
        var array = new Array();
        var i = 0;
        $('.chk-delete-user:checked').each(function(index, element){
            array[i] = element.value;
            i++;
        });
        parameters.userIds = array;
        var route = Routing.generate('claro_workspace_delete_users', {'workspaceId': twigWorkspaceId});
        route+='?'+$.param(parameters);
        Claroline.Utilities.ajax({
            url: route,
            success: function(){
                $('.chk-delete-user:checked').each(function(index, element){
                     $(element).parent().parent().remove();
                });
                $('#validation-box').modal('hide');
                $('#validation-box-body').empty();
                $('.delete-users-button').attr('disabled', 'disabled');
            },
            type: 'DELETE'
        });
    });

    $('#modal-cancel-button').click(function(){
        $('#validation-box').modal('hide');
        $('#validation-box-body').empty();
    });

    function lazyloadUsers(route) {
        loading = true;
        $('#user-loading').show();
        Claroline.Utilities.ajax({
            url: route(),
            type: 'GET',
            success: function(users){
                $('#user-table-body').append(Twig.render(user_list, {'users': users}));
                loading = false;
                $('#user-loading').hide();
                if(users.length == 0) {
                    stop = true;
                }
            },
            complete: function(){
                if($(window).height() >= $(document).height() && stop == false){
                    lazyloadUsers(route)
                }
            }
        });
    }
})()
