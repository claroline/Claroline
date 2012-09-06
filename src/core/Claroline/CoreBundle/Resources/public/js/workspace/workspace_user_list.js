(function(){
    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var twigDeleteTranslation = document.getElementById('twig-attributes').getAttribute('data-translation.delete');

    var nbIterationUsers=0;
    var nbIterationGroups=0;

    $('#user-loading').hide();

    $('#bootstrap-modal').modal({
        show: false,
        backdrop: false
    });

    $('.link-delete-user').live('click', function(e){
        e.preventDefault();
        var route = $(this).attr('href');
        var element = $(this).parent();
        ClaroUtils.sendRequest(route, function(data){
            element.remove();
        })
    })

    $('#bootstrap-modal').on('hidden', function(){
        /*$('#modal-login').empty();
        $('#modal-body').show();*/
        //the page must be reloaded or it'll break dynatree
        if ($('#modal-login').find('form').attr('id') == 'login_form'){
            window.location.reload();
        }
    })

    $('#add-user-button').click(function(){
         $('#bootstrap-modal-user').modal('show');
    });

    $('#add-group-button').click(function(){
         $('#bootstrap-modal-group').modal('show');
    });

    $('#btn-save-users').on('click', function(event){
        var parameters = {};
        var i = 0;
        $('.checkbox-user-name:checked').each(function(index, element){
            parameters[i] = element.value;
            i++;
        })
        parameters.workspaceId = twigWorkspaceId;
        var route = Routing.generate('claro_ws_multiadd_user', parameters);
        ClaroUtils.sendRequest(route, function(data){createUserCallbackLi(data)})
        $('#bootstrap-modal-user').modal('hide');
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        nbIterationUsers = 0;
    });

    $('#group_checkboxes').on('click', '.checkbox_group_name', function(event){
        var groupId = $(this).val();
        if( $(this).is(':checked')){
            setOnAddGroupChkBox(groupId);
        } else {
            setOnRemoveGroupChkBox(groupId);
        }
    });

    $('#lazy-load-user-button').click(function(){
        $('#user-loading').show();
        var route = Routing.generate('claro_ws_users_limited_list', {'workspaceId': twigWorkspaceId, 'nbIteration': nbIterationUsers, 'format':'json'});
        ClaroUtils.sendRequest(
            route,
            function(data){
                if (nbIterationUsers == 0){
                    $('.checkbox-user-name').remove();
                    $('#user-table-checkboxes-body').empty();
                }
                nbIterationUsers++;
                createUsersChkBoxes(data);
                $('#user-loading').hide();
            }
        );
    });

    $('#add_next_groups').click(function(){
        $('#group_loading').show();
        sendRequest(
            'claro_ws_groups_limited_list',
            {'workspaceId': twigWorkspaceId, 'nbIteration': nbIterationGroups, 'format':'json'},
            function(data){
                if (nbIterationGroups == 0){
                    $('.checkbox_group_name').remove();
                    $('#group_checkboxes').empty();
                }
                nbIterationGroups++;
                createGroupsChkBoxes(data);
                $('#group_loading').hide();
            }
        );
    });

    $('#search-user-button').click(function(){
        $('#user-loading').show();
        var search = document.getElementById('search-user-txt').value;
        nbIterationUsers = 0;
        var route = Routing.generate('claro_ws_search_unregistered_users_by_names', {'search': search, 'workspaceId': twigWorkspaceId, 'format': 'json'})
        ClaroUtils.sendRequest(
            route,
            function(data){
                $('.checkbox-user-name').remove();
                $('#user-table-checkboxes-body').empty();
                createUsersChkBoxes(data);
                $('#user-loading').hide();
            }
        );
    });

    $('#search_group_button').click(function(){
        $('#group_loading').show();
        var search = document.getElementById('search_group_txt').value;
        nbIterationGroups = 0;
        sendRequest(
            'claro_ws_search_unregistered_groups_by_name',
            {'search': search, 'workspaceId': twigWorkspaceId, 'format': 'json'},
            function(data){
                $('.checkbox_group_name').remove();
                $('#group_checkboxes').empty();
                createGroupsChkBoxes(data);
                $('#group_loading').hide();
            }
        );
    });

    function setOnAddGroupChkBox(groupId)
    {
        sendRequest(
            'claro_ws_add_group',
            {'groupId': groupId, 'workspaceId': twigWorkspaceId},
            function(data){
                $('#workspace_groups').append(data);
            }
        );
    }

    function setOnRemoveGroupChkBox(groupId)
    {
        var route = Routing.generate('claro_ws_remove_group', {'groupId': groupId, 'workspaceId': twigWorkspaceId});
        ClaroUtils.sendRequest(
            route,
            function(data){
                $('#group_' + groupId).remove();
            }
        );
    }

    function createGroupsChkBoxes(JSONString)
    {
        JSONObject = eval(JSONString);
        //chkboxes creation
        var i=0;
        while (i<JSONObject.length)
        {
            var list = '<tr>'
            +'<td><input class="checkbox_group_name" id="checkbox_group_'+JSONObject[i].id+'" type="checkbox" value="'+JSONObject[i].id+'" id="checkbox_group_'+JSONObject[i].id+'">'+JSONObject[i].name+'</input></td>'
            +'</tr>';
            $('#group_checkboxes').append(list);
            i++;
        }
    }

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
            +'<td align="center">'+JSONObject[i].lastName+'</td>'
            +'<td align="center">'+JSONObject[i].firstName+'</td>'
            +'</tr>';
            $('#user-table-checkboxes-body').append(list);
            i++;
        }
    }

    function createUserCallbackLi(JSONString)
    {
        console.debug(JSONString);
        JSONObject = eval(JSONString);
        var i=0;
        while (i<JSONObject.length)
        {
            var li = '<li class="row-user" id="user-'+JSONObject[i].id+'">'+JSONObject[i].username
            +'<a href="'+Routing.generate('claro_ws_remove_user', {'userId':JSONObject[i].id, 'workspaceId':twigWorkspaceId})+' id="link_delete_user_'+JSONObject[i].id+'" class="link-delete-user"> '+twigDeleteTranslation+'</a>'
            +'</li>';
            $('#workspace-users').append(li);
            i++;
        }
    }
})()
