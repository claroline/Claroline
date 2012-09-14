(function(){
    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var nbIterationUsers = 1;
    var modeModal = 0;
    var modeBackground = 0;
    var stopModal = false;
    var stopBackground = false;
    var loading = false;
    var page = 1;

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
        nbIterationUsers = 1;
    });

    $('#reset-modal-button').click(function(){
        window.scrollTo(0, document.getElementById('modal-body').offsetTop);
        modeModal = 0;
        stopModal = false;
        $('.modal-body').animate({scrollTop: 0}, 0);
        nbIterationUsers = 1
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        lazyloadUnregisteredUsers();
    });

    $('#search-modal-user-button').click(function(){
        nbIterationUsers = modeModal = 1;
        stopModal = false;
        $('.modal-body').animate({scrollTop: 0}, 0);
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        lazyloadSearchUnregisteredUsers();
    });

    $('#search-background-user-button').click(function(){
        $('#user-table-body').empty();
        page = modeBackground = 1;
        stopBackground = false;
        lazyloadSearchRegisteredUsers();
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
        var route = Routing.generate('claro_workspace_unregistered_users_paginated', {'workspaceId': twigWorkspaceId, 'page': nbIterationUsers});
        ClaroUtils.sendRequest(
            route,
            function(data){
                console.debug(data.length);
                if(data.length == 0){
                    stopModal = true;
                }
                if (nbIterationUsers == 1){
                    $('.checkbox-user-name').remove();
                    $('#user-table-checkboxes-body').empty();
                }
                createUsersChkBoxes(data);
                $('#user-loading').hide();
            },
            function(){
                loading = false;
            }
        );
        nbIterationUsers++;
    }

    function lazyloadRegisteredUsers()
    {
        loading = true;
        ClaroUtils.sendRequest(
            Routing.generate('claro_workspace_registered_users_paginated', {
                'workspaceId':twigWorkspaceId,
                'page':nbIterationUsers
            }),
            function(users){
                if(users.length == 0){
                    stopModal = true;
                }
                nbIterationUsers++;
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
                'page': page
            })
            ClaroUtils.sendRequest(
                route,
                function(users){
                    if(users.length == 0){
                        stopBackground = true;
                    }
                    page++;
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
                'page': nbIterationUsers
            })
            ClaroUtils.sendRequest(
                route,
                function(users){
                    if(users.length == 0){
                        stopBackground = true;
                    }
                    if (nbIterationUsers == 1){
                        $('.checkbox-user-name').remove();
                        $('#user-table-checkboxes-body').empty();
                    }
                    createUsersChkBoxes(users);
                    $('#user-loading').hide()
                    nbIterationUsers++;
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
                'page':page
            }),
            function(users){
                if(users.length == 0){
                    stopBackground = true;
                }
                page++;
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
