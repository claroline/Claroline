(function(){
    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var twigDeleteTranslation = document.getElementById('twig-attributes').getAttribute('data-translation.delete');
    var lazyloading = false;
    var nbIterationGroups=0;

    $('#group-loading').hide();

    $('#bootstrap-modal').modal({
        show: false,
        backdrop: false
    });

    $('.modal-body').scroll(function(){
        if  (($('.modal-body')[0].scrollHeight - ($('.modal-body').scrollTop() + $('.modal-body').height())) <= 100 && lazyloading == true){
            lazyload(twigWorkspaceId, nbIterationGroups);
            nbIterationGroups++;
        }
    });


    $('.link-delete-group').live('click', function(e){
        var route = Routing.generate('claro_workspace_delete_group', {'groupId': $(this).attr('data-group-id'), 'workspaceId': twigWorkspaceId});
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

    $('#bootstrap-modal').on('hidden', function(){
        /*$('#modal-login').empty();
        $('#modal-body').show();*/
        //the page must be reloaded or it'll break dynatree
        if ($('#modal-login').find('form').attr('id') == 'login_form'){
            window.location.reload();
        }
    })

    $('#add-group-button').click(function(){
         $('#bootstrap-modal-group').modal('show');
    });

    $('#btn-save-groups').on('click', function(event){
        var parameters = {};
        var i = 0;
        $('.checkbox-group-name:checked').each(function(index, element){
            parameters[i] = element.value;
            i++;
        })
        parameters.workspaceId = twigWorkspaceId;
        var route = Routing.generate('claro_workspace_multiadd_group', parameters);
        ClaroUtils.sendRequest(
            route,
            function(data){createGroupCallBackLi(data)},
            undefined,
            'PUT'
        );
        $('#bootstrap-modal-group').modal('hide');
        $('.checkbox-group-name').remove();
        $('#group-checkboxes').empty();
        nbIterationGroups = 0;
    });

    $('#reset-button').click(function(){
        $('.modal-body').animate({scrollTop: 0}, 1000);
        lazyloading = true;
        nbIterationGroups = 0;
        lazyload(twigWorkspaceId, nbIterationGroups)
        nbIterationGroups++;
    });

    $('#search-group-button').click(function(){
        lazyloading = false;
        var search = document.getElementById('search-group-txt').value;
        if (search != '')  {
            $('#group-loading').show();
            nbIterationGroups = 0;
            var route = Routing.generate('claro_workspace_search_unregistered_groups',
            {'search': search, 'workspaceId': twigWorkspaceId});
            ClaroUtils.sendRequest(
                route,
                function(data){
                    $('.checkbox-group-name').remove();
                    $('#group-table-checkboxes-body').empty();
                    createGroupsChkBoxes(data);
                    $('#group-loading').hide();
                }
            );
        }
    });

    function createGroupsChkBoxes(JSONString)
    {
        JSONObject = eval(JSONString);
        //chkboxes creation
        var i=0;
        while (i<JSONObject.length)
        {
            var row = '<tr>'
            +'<td align="center"><input class="checkbox-group-name" id="checkbox-group-'+JSONObject[i].id+'" type="checkbox" value="'+JSONObject[i].id+'" id="checkbox-group-'+JSONObject[i].id+'"></input></td>'
            +'<td align="center">'+JSONObject[i].name+'</td>'
            +'</tr>';
            $('#group-table-checkboxes-body').append(row);
            i++;
        }
    }

    function createGroupCallBackLi(JSONString)
    {
        JSONObject = eval(JSONString);

        var i=0;
        while (i<JSONObject.length)
        {
            var row = '<tr class="row-group">'
            + '<td align="center">'+JSONObject[i].name+'</td>'
            + '<td>'
            + '<a href="#" data-group-id="'+JSONObject[i].id+'" id="link-delete-group-'+JSONObject[i].id+'" class="link-delete-group">'+twigDeleteTranslation+"</a>"
            + '</td>'
            $('#body-tab-group').append(row);
            i++;
        }
    }

    function lazyload(twigWorkspaceId, nbIterationGroups)
    {
        $('#group-loading').show();
        var route = Routing.generate('claro_workspace_groups_paginated', {
            'workspaceId': twigWorkspaceId,
            'page': nbIterationGroups
        })
        ClaroUtils.sendRequest(
            route,
            function(data){
                if (nbIterationGroups == 0){
                    $('.checkbox-group-name').remove();
                    $('#group-table-checkboxes-body').empty();
                }
                nbIterationGroups++;
                createGroupsChkBoxes(data);
                $('#group-loading').hide();
            }
            );
    }

})()
