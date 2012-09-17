(function(){
    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var offsetModalUsers = 0;
    var modeModal = 0;
    var modeBackground = 0;
    var stopModal = false;
    var stopBackground = false;
    var loading = false;
    var offsetBackgroundUsers = 0;

    lazyloadRegisteredUsers();

    $('#user-loading').hide();

    $('.modal-body').scroll(function(){
        if(stopModal != true && loading == false){
            if  (($('.modal-body')[0].scrollHeight - ($('.modal-body').scrollTop() + $('.modal-body').height())) <= 100) {
                if (modeModal == 0){
                    lazyloadUnregisteredUsers();
                } else {
                    lazyloadSearchUnregisteredUsers();
                }
            }
        }
    });

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && stopBackground == false && loading == false){
            if(modeBackground == 0){
                lazyloadRegisteredUsers();
            }else{
                lazyloadSearchRegisteredUsers();
            }
        }
    });

    $('#add-user-button').click(function(){
        window.scrollTo(0, document.getElementById('modal-body').offsetTop);
        $('#bootstrap-modal-user').modal('show');
    });

    $('.link-delete-user').live('click', function(e){
        e.preventDefault();
        var route = Routing.generate('claro_workspace_delete_user', {'userId': $(this).attr('data-user-id'), 'workspaceId': twigWorkspaceId});
        var element = $(this).parent().parent();
        ClaroUtils.sendRequest(
            route,
            function(data){
                element.remove();
                offsetBackgroundUsers--;
            },
            undefined,
            'DELETE'
            )
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

    $('#btn-save-users').on('click', function(event){
        var parameters = {};
        var i = 0;
        $('.checkbox-user-name:checked').each(function(index, element){
            parameters[i] = element.value;
            i++;
        })
        parameters.workspaceId = twigWorkspaceId;
        var route = Routing.generate('claro_workspace_multiadd_user', parameters);
        ClaroUtils.sendRequest(
            route,
            function(data){
                if (stopBackground == true) {
                    createUserCallback(data)
                }
            },
            undefined,
            'PUT'
        )
        $('#bootstrap-modal-user').modal('hide');
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        offsetModalUsers = 0;
    });

    $('#reset-modal-button').click(function(){
        window.scrollTo(0, document.getElementById('modal-body').offsetTop);
        modeModal = 0;
        stopModal = false;
        $('.modal-body').animate({scrollTop: 0}, 0);
        offsetModalUsers = 0
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        lazyloadUnregisteredUsers();
    });

    $('#search-modal-user-button').click(function(){
        offsetModalUsers = 0;
        modeModal = 1;
        stopModal = false;
        $('.modal-body').animate({scrollTop: 0}, 0);
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        lazyloadSearchUnregisteredUsers();
    });

    $('#search-background-user-button').click(function(){
        $('#user-table-body').empty();
        offsetBackgroundUsers = 0;
        modeBackground = 1;
        stopBackground = false;
        lazyloadSearchRegisteredUsers();
    });

    $('#reset-background-user-button').click(function(){
        $('#user-table-body').empty();
        modeBackground = 1;
        offsetBackgroundUsers = 0
        stopBackground = false;
        lazyloadRegisteredUsers();
    });

    function createUsersChkBoxes(JSONString)
    {
        var i=0;
        while (i<JSONString.length)
        {
            var list = '<tr>'
            +'<td align="center"><input class="checkbox-user-name" id="checkbox-user-'+JSONString[i].id+'" type="checkbox" value="'+JSONString[i].id+'" id="checkbox-user-'+JSONString[i].id+'"></input></td>'
            +'<td align="center">'+JSONString[i].username+'</td>'
            +'<td align="center">'+JSONString[i].lastname+'</td>'
            +'<td align="center">'+JSONString[i].firstname+'</td>'
            +'</tr>';
            $('#user-table-checkboxes-body').append(list);
            i++;
        }
    }

    function createUserCallback(users)
    {
            var render = Twig.render(user_list, {'users': users});
            $('#user-table-body').append(render);
    }

    function lazyloadUnregisteredUsers()
    {
        loading = true;
        $('#user-loading').show();
        var route = Routing.generate('claro_workspace_unregistered_users_paginated', {'workspaceId': twigWorkspaceId, 'offset': offsetModalUsers});
        ClaroUtils.sendRequest(
            route,
            function(data){
                console.debug(data.length);
                if(data.length == 0){
                    stopModal = true;
                }
                if (offsetModalUsers == 0){
                    $('.checkbox-user-name').remove();
                    $('#user-table-checkboxes-body').empty();
                }
                createUsersChkBoxes(data);
                $('#user-loading').hide();
                offsetModalUsers+=data.length;
            },
            function(){
                loading = false;
            }
        );
    }

    function lazyloadRegisteredUsers()
    {
        loading = true;
        ClaroUtils.sendRequest(
            Routing.generate('claro_workspace_registered_users_paginated', {
                'workspaceId':twigWorkspaceId,
                'offset':offsetModalUsers
            }),
            function(users){
                if(users.length == 0){
                    stopModal = true;
                }
                offsetModalUsers+=users.length;
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

    function lazyloadSearchRegisteredUsers()
    {
        loading = true;
        var search = document.getElementById('search-background-user-txt').value;
        if (search !== ''){
            var route = Routing.generate('claro_workspace_search_registered_users', {
                'search': search,
                'workspaceId': twigWorkspaceId,
                'offset': offsetBackgroundUsers
            })
            ClaroUtils.sendRequest(
                route,
                function(users){
                    if(users.length == 0){
                        stopBackground = true;
                    }
                    offsetBackgroundUsers+=users.length;
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

    function lazyloadSearchUnregisteredUsers()
    {
        loading = true;
        var search = document.getElementById('search-modal-user-txt').value;
        if (search !== ''){
            var route = Routing.generate('claro_workspace_search_unregistered_users', {
                'search': search,
                'workspaceId': twigWorkspaceId,
                'offset': offsetModalUsers
            })
            ClaroUtils.sendRequest(
                route,
                function(users){
                    if(users.length == 0){
                        stopBackground = true;
                    }
                    if (offsetModalUsers == 0){
                        $('.checkbox-user-name').remove();
                        $('#user-table-checkboxes-body').empty();
                    }
                    createUsersChkBoxes(users);
                    $('#user-loading').hide()
                    offsetModalUsers+=users.length;
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
                'offset':offsetBackgroundUsers
            }),
            function(users){
                if(users.length == 0){
                    stopBackground = true;
                }
                offsetBackgroundUsers+=users.length;
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
})()
