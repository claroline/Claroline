(function () {
    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var loading = false;
    var stop = false;
    var mode = 0; //0 = standard || 1 = search
    var callbackLength = 0;

    addContent(function(){lazyloadUnregisteredUsers()});

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && stop == false && loading == false){
            if(mode == 0){
                lazyloadUnregisteredUsers();
            }else{
                lazyloadSearchUnregisteredUsers();
            }
        }
    });

    function lazyloadUnregisteredUsers()
    {
        loading = true;
        $('#user-loading').show();
        var route = Routing.generate('claro_workspace_unregistered_users_paginated', {'workspaceId': twigWorkspaceId, 'offset': $('.row-user').length});
        ClaroUtils.sendRequest(
            route,
            function(data){
                if(data.length == 0){
                    stop = true;
                }
                createUsersChkBoxes(data);
                $('#user-loading').hide();
            },
            function(){
                loading = false;
            }
        );
    }

    function lazyloadSearchUnregisteredUsers()
    {
        loading = true;
        var search = document.getElementById('search-modal-user-txt').value;
        if (search !== ''){
            var route = Routing.generate('claro_workspace_search_unregistered_users', {
                'search': search,
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-user').length
            })
            ClaroUtils.sendRequest(
                route,
                function(users){
                    if(users.length == 0){
                        stop = true;
                    }
                    createUsersChkBoxes(users);
                    $('#user-loading').hide()
                },
                function(){
                    loading = false;
                }
            );
        }
    }

    $('#reset-button').click(function(){
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        lazyloadUnregisteredUsers();
    });

    $('#search-button').click(function(){
        mode = 1;
        $('.modal-body').animate({
            scrollTop: 0
        }, 0);
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        lazyloadSearchUnregisteredUsers();
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
            function(users){alert(users.length+' users added to the workspace')},
            undefined,
            'PUT'
        )
        $('.checkbox-user-name:checked').each(function(index, element){
             $(element).parent().parent().remove();
        })
    });

    function createUsersChkBoxes(JSONString)
    {
        var i=0;
        while (i<JSONString.length)
        {
            var list = '<tr class="row-user">'
            +'<td align="center"><input class="checkbox-user-name" id="checkbox-user-'+JSONString[i].id+'" type="checkbox" value="'+JSONString[i].id+'" id="checkbox-user-'+JSONString[i].id+'"></input></td>'
            +'<td align="center">'+JSONString[i].username+'</td>'
            +'<td align="center">'+JSONString[i].lastname+'</td>'
            +'<td align="center">'+JSONString[i].firstname+'</td>'
            +'</tr>';
            $('#user-table-checkboxes-body').append(list);
            i++;
        }
    }

    function addContent(callBack){
        callBack();
        if($(window).height() >= $(document).height() && callbackLength != 0 && loading == false){
            addContent(callBack);
        }
    }

})();