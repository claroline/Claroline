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

    $('.button-delete-user').live('click', function(e){
        e.preventDefault();
        var route = Routing.generate('claro_workspace_delete_user', {'userId': $(this).attr('data-user-id'), 'workspaceId': twigWorkspaceId});
        var element = $(this).parent().parent();
        ClaroUtils.sendRequest(
            route,
            function(data){
                element.remove();
            },
            undefined,
            'DELETE'
            )
    })

    $('.button-parameters-user').live('click', function(e){
        var route = Routing.generate(
            'claro_workspace_tools_user_parameters',
            {'userId': $(this).attr('data-user-id'), 'workspaceId': twigWorkspaceId}
        );

        window.location.href = route;
    })

    $('#bootstrap-modal').modal({
        show: false,
        backdrop: false
    });

    $('#bootstrap-modal').on('hidden', function(){
        if ($('#modal-login').find('form').attr('id') == 'login_form'){
            window.location.reload();
        }
    })

    $('#search-background-user-button').click(function(){
        $('#user-table-body').empty();
        mode = 1;
        stopBackground = false;
        addContent(function(){lazyloadSearchRegisteredUsers()});
    });

    $('#reset-background-user-button').click(function(){
        $('#user-table-body').empty();
        mode = 1;
        stopBackground = false;
        addContent(function(){lazyloadRegisteredUsers()});
    });

    function lazyloadSearchRegisteredUsers()
    {
        loading = true;
        var search = document.getElementById('search-background-user-txt').value;
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
