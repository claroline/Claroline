(function(){
    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var mode = 0; //0 = standard || 1 = search
    var stopBackground = false;
    var loading = false;
    var callBackLength = 1;

    addContent(function(){lazyloadRegisteredUsers()});

    $('#user-loading').hide();

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && stopBackground == false && loading == false){
            if(mode == 0){
                lazyloadRegisteredUsers();
            }else{
                lazyloadSearchRegisteredUsers();
            }
        }
    });

    $('.button-parameters-user').live('click', function(e){
        var route = Routing.generate(
            'claro_workspace_tools_show_group_parameters',
            {'userId': $(this).attr('data-user-id'), 'workspaceId': twigWorkspaceId}
        );

        window.location.href = route;
    })

    $('#search-user-button').click(function(){
        $('#user-table-body').empty();
        mode = 1;
        stopBackground = false;
        addContent(function(){lazyloadSearchRegisteredUsers()});
    });

    $('#delete-user-button').click(function(){
        $('#validation-box').modal('show');
        $('#validation-box-body').html('removing '+ $('.chk-delete-user:checked').length +' user(s)');
    });

    $('#modal-valid-button').click(function(){
        var parameters = {};
        var i = 0;
        $('.chk-delete-user:checked').each(function(index, element){
            parameters[i] = element.value;
            i++;
        });

        parameters.workspaceId = twigWorkspaceId;
        var route = Routing.generate('claro_workspace_delete_users', parameters);
        ClaroUtils.sendRequest(
            route,
            function(){
                $('.chk-delete-user:checked').each(function(index, element){
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

    $('#reset-user-button').click(function(){
        $('#user-table-body').empty();
        mode = 1;
        stopBackground = false;
        addContent(function(){lazyloadRegisteredUsers()});
    });

    function lazyloadSearchRegisteredUsers()
    {
        loading = true;
        var search = document.getElementById('search-user-txt').value;
        if (search !== ''){
            var route = Routing.generate('claro_workspace_search_registered_users', {
                'search': search,
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-user').length
            })
            ClaroUtils.sendRequest(
                route,
                function(users){
                    if(users.length == 0){
                        stopBackground = true;
                    }

                    callBackLength = users.length;
                    var render = Twig.render(user_list, {
                        'users': users
                    });
                    $('#user-table-body').append(render);
                },
                function(){
                    loading = false;
                }
            );
        }
    }

    function lazyloadRegisteredUsers()
    {
        loading = true;
        ClaroUtils.sendRequest(
            Routing.generate('claro_workspace_registered_users_paginated', {
                'workspaceId':twigWorkspaceId,
                'offset': $('.row-user').length
            }),
            function(users){
                if(users.length == 0){
                    stopBackground = true;
                }

                callBackLength = users.length;
                var render = Twig.render(user_list, {
                    'users': users
                });
                $('#user-table-body').append(render);
            },
            function(){
                loading = false;
            }
        );
    }

    function addContent(callBack){
        callBack();
        if($(window).height() >= $(document).height() && callBackLength != 0 && loading == false){
            addContent(callBack);
        }
    }
})()
