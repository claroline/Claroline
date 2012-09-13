(function(){
    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var lazyloading = false;
    var nbIterationUsers = 1;

    $('#user-loading').hide();
    $('.modal-body').scroll(function(){
        if  (($('.modal-body')[0].scrollHeight - ($('.modal-body').scrollTop() + $('.modal-body').height())) <= 100 && lazyloading == true){
            lazyload(twigWorkspaceId, nbIterationUsers);
            nbIterationUsers++;
        }
    });

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && lazyloading === false){
            lazyloading = true;
            $('#loading').show();
            var route = Routing.generate('claro_workspace_registered_users_paginated', {
                'workspaceId':twigWorkspaceId,
                'page':page
            })
            ClaroUtils.sendRequest(route, function(users){
                page++;
                $('#user-table-body').append(Twig.render(user_list, {
                    'users': users
                }));
                lazyloading = false;
                $('#loading').hide();
            })
        }
    });

    var page = 1;

    ClaroUtils.sendRequest(
        Routing.generate('claro_workspace_registered_users_paginated', {
            'workspaceId':twigWorkspaceId,
            'page':page
        }),
        function(users){
            page++;
            var render = Twig.render(user_list, {'users': users});
            $('#user-table-body').append(render);
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
        /*$('#modal-login').empty();
        $('#modal-body').show();*/
        //the page must be reloaded or it'll break dynatree
        if ($('#modal-login').find('form').attr('id') == 'login_form'){
            window.location.reload();
        }
    })

    $('#add-user-button').click(function(){
        window.scrollTo(0, document.getElementById('modal-body').offsetTop);
        $('#bootstrap-modal-user').modal('show');
    });

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
            function(data){createUserCallback(data)},
            undefined,
            'PUT'
        )
        $('#bootstrap-modal-user').modal('hide');
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        nbIterationUsers = 1;
    });

    $('#reset-button').click(function(){
        $('.modal-body').animate({scrollTop: 0}, 0);
        lazyloading = true;
        nbIterationUsers = 1
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        lazyload(twigWorkspaceId, nbIterationUsers);
        nbIterationUsers++;
    });

    $('#search-user-button').click(function(){
        lazyloading = false;
        var search = document.getElementById('search-user-txt').value;
        if (search !== ''){
            $('#user-loading').show();
            nbIterationUsers = 1;
            var route = Routing.generate('claro_workspace_search_unregistered_users', {'search': search, 'workspaceId': twigWorkspaceId})
            ClaroUtils.sendRequest(
                route,
                function(data){
                    $('.checkbox-user-name').remove();
                    $('#user-table-checkboxes-body').empty();
                    createUsersChkBoxes(data);
                    $('#user-loading').hide();
                }
            );
        }
    });

    function createUsersChkBoxes(JSONString)
    {
        JSONObject = eval(JSONString);
        //chkboxes creation
        var i=0;
        while (i<JSONObject.length)
        {
            var list = '<tr>'
            +'<td align="center"><input class="checkbox-user-name" id="checkbox-user-'+JSONObject[i].id+'" type="checkbox" value="'+JSONObject[i].id+'" id="checkbox-user-'+JSONObject[i].id+'"></input></td>'
            +'<td align="center">'+JSONObject[i].username+'</td>'
            +'<td align="center">'+JSONObject[i].lastname+'</td>'
            +'<td align="center">'+JSONObject[i].firstname+'</td>'
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

    function lazyload(twigWorkspaceId, nbIterationUsers)
    {
        $('#user-loading').show();
        var route = Routing.generate('claro_workspace_unregistered_users_paginated', {'workspaceId': twigWorkspaceId, 'page': nbIterationUsers});
        ClaroUtils.sendRequest(
            route,
            function(data){
                if (nbIterationUsers == 1){
                    $('.checkbox-user-name').remove();
                    $('#user-table-checkboxes-body').empty();
                }
                createUsersChkBoxes(data);
                $('#user-loading').hide();
            }
        );
    }
})()
