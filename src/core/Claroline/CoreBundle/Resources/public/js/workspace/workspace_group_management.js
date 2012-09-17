(function(){
    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var lazyloading = false;
    var offsetModalGroups = 0;

    $('#group-loading').hide();

    $('#bootstrap-modal').modal({
        show: false,
        backdrop: false
    });

    var offsetBackgroundGroups = 0;

    ClaroUtils.sendRequest(
        Routing.generate('claro_workspace_registered_groups_paginated', {
            'workspaceId':twigWorkspaceId,
            'offset':offsetBackgroundGroups
        }),
        function(groups){
            offsetBackgroundGroups += groups.length;
            var render = Twig.render(group_list, {'groups': groups});
            $('#group-table-body').append(render);
    });

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && lazyloading === false){
            lazyloading = true;
            $('#loading').show();
            var route = Routing.generate('claro_workspace_registered_groups_paginated', {
                'workspaceId':twigWorkspaceId,
                'offset':offsetBackgroundGroups
            })
            ClaroUtils.sendRequest(route, function(groups){
                offsetBackgroundGroups += groups.length;
                $('#group-table-body').append(Twig.render(group_list, {
                    'groups': groups
                }));
                lazyloading = false;
            })
        }
    });

    $('.modal-body').scroll(function(){
        if  (($('.modal-body')[0].scrollHeight - ($('.modal-body').scrollTop() + $('.modal-body').height())) <= 100 && lazyloading == true){
            lazyload(twigWorkspaceId);
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
        offsetModalGroups = 0;
    });

    $('#reset-button').click(function(){
        $('.modal-body').animate({scrollTop: 0}, 0);
        lazyloading = true;
        offsetModalGroups = 0;
        lazyload(twigWorkspaceId)
    });

    $('#search-group-button').click(function(){
        lazyloading = false;
        var search = document.getElementById('search-group-txt').value;
        if (search != '')  {
            $('#group-loading').show();
            offsetModalGroups = 0;
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

    function createGroupCallBackLi(groups)
    {
        var render = Twig.render(group_list, {'groups': groups});
        $('#group-table-body').append(render);
    }

    function lazyload(twigWorkspaceId)
    {
        $('#group-loading').show();
        var route = Routing.generate('claro_workspace_unregistered_groups_paginated', {
            'workspaceId': twigWorkspaceId,
            'offset': offsetModalGroups
        })
        ClaroUtils.sendRequest(
            route,
            function(data){
                if (offsetModalGroups == 0){
                    $('.checkbox-group-name').remove();
                    $('#group-table-checkboxes-body').empty();
                }
                offsetModalGroups+=data.length;
                createGroupsChkBoxes(data);
                $('#group-loading').hide();
            }
            );
    }

})()
