(function () {
    $('html, body').animate({
        scrollTop: 0
    }, 0);

    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var loading = false;
    var stop = false;
    var mode = 0; //0 = standard || 1 = search

    lazyloadGroups(getStandardRoute());

   $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && stop == false && loading == false){
            if(mode == 0){
                lazyloadGroups(getStandardRoute());
            } else {
                lazyloadGroups(getSearchRoute());
            }
        }
    });

    $('.btn-save-groups').on('click', function(event){
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
            function(groups){
                alert(groups.length+' groups added to the workspace')
                },
            undefined,
            'PUT'
            )
        $('.checkbox-group-name:checked').each(function(index, element){
            $(element).parent().parent().remove();
        })
    });

    $('.search-group-button').click(function(){
        $('.checkbox-group-name').remove();
        $('#group-table-body').empty();
        stop = false;
        if (document.getElementById('search-group-txt').value != ''){
            mode = 1;
            lazyloadGroups(getSearchRoute());
        } else {
            mode = 0;
            lazyloadGroups(getStandardRoute());
        }
    });

    function createGroupsChkBoxes(JSONObject)
    {
        var i=0;

        while (i<JSONObject.length)
        {
            var row = '<tr class="row-group">'
            +'<td align="center"><input class="checkbox-group-name" id="checkbox-group-'+JSONObject[i].id+'" type="checkbox" value="'+JSONObject[i].id+'" id="checkbox-group-'+JSONObject[i].id+'"></input></td>'
            +'<td align="center">'+JSONObject[i].name+'</td>'
            +'</tr>';
            $('#group-table-body').append(row);
            i++;
        }
    }

    function lazyloadGroups(route){
        loading = true;
        $('#group-loading').show();
        ClaroUtils.sendRequest(
            route,
            function(groups){
                if (groups.length == 0){
                    stop = true;
                }
                createGroupsChkBoxes(groups);
                $('#group-loading').hide();
            },
            function(){
                loading = false;
            });
    }

    function getStandardRoute(){
        return Routing.generate('claro_workspace_unregistered_groups_paginated', {
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-group').length
            })
    }

    function getSearchRoute(){
        return Routing.generate('claro_workspace_search_unregistered_groups', {
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-group').length,
                'search': document.getElementById('search-group-txt').value
            })
    }
})();
