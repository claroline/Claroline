(function () {
    $('html, body').animate({
        scrollTop: 0
    }, 0);

    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var loading = false;
    var stop = false;
    var mode = 0; //0 = standard || 1 = search

    $('.loading').hide();
    $('.add-groups-button').attr('disabled', 'disabled');

    $('.chk-grp').live('change', function(){
        if ($('.chk-grp:checked').length){
           $('.add-groups-button').removeAttr('disabled');
        } else {
           $('.add-groups-button').attr('disabled', 'disabled');
        }
    })



    var standardRoute = function(){
        return Routing.generate('claro_workspace_unregistered_groups_paginated', {
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-group').length
            });
    }

    var searchRoute = function(){
        return Routing.generate('claro_workspace_search_unregistered_groups', {
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-group').length,
                'search': document.getElementById('search-group-txt').value
            });
    }

    lazyloadGroups(standardRoute);

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false && stop === false){
            if(mode == 0){
                lazyloadGroups(standardRoute);
            } else {
                lazyloadGroups(searchRoute);
            }
        }
    });

    $('.add-groups-button').on('click', function(event) {
        $('#validation-box').modal('show');
        $('#validation-box-body').html(Twig.render(add_group_confirm, { 'nbGroups' :$('.chk-grp:checked').length} ));
    });

    $('#modal-valid-button').on('click', function(event) {
        var parameters = {};
        var array = new Array();
        var i = 0;
        $('.chk-grp:checked').each(function(index, element){
            array[i] = element.value;
            i++;
        })
        parameters.ids = array;
        var route = Routing.generate('claro_workspace_multiadd_group', {'workspaceId': twigWorkspaceId});
        route+='?'+$.param(parameters);
        $('#adding').show();
        Claroline.Utilities.ajax({
            url: route,
            success: function(groups){
                $('#validation-box').modal('hide');
                $('#validation-box-body').empty();
                $('.add-groups-button').attr('disabled', 'disabled');
                $('#adding').hide();
                $('.chk-grp:checked').each(function(index, element){
                    $(element).parent().parent().remove();
                })
                $('.add-groups-button').attr('disabled', 'disabled');
                },
            type: 'PUT'
        })

    });

    $('.search-group-button').click(function(){
        $('.chk-grp').remove();
        $('#group-table-body').empty();
        stop = false;
        if (document.getElementById('search-group-txt').value != ''){
            mode = 1;
            lazyloadGroups(searchRoute);
        } else {
            mode = 0;
            lazyloadGroups(standardRoute);
        }
    });

    function createGroupsChkBoxes(JSONObject)
    {
        var i=0;

        while (i<JSONObject.length)
        {
            var row = '<tr class="row-group">'
            +'<td align="center">'+JSONObject[i].name+'</td>'
            +'<td align="center"><input class="chk-grp" id="checkbox-group-'+JSONObject[i].id+'" type="checkbox" value="'+JSONObject[i].id+'" id="checkbox-group-'+JSONObject[i].id+'"></input></td>'
            +'</tr>';
            $('#group-table-body').append(row);
            i++;
        }
    }

    function lazyloadGroups(route){
        loading = true;
        $('#loading').show();
        Claroline.Utilities.ajax({
            url: route(),
            success: function(groups){
                createGroupsChkBoxes(groups);
                loading = false;
                $('#loading').hide();
                if (groups.length == 0){
                    stop = true;
                }
            },
            complete: function(){
                if($(window).height() >= $(document).height() && stop == false){
                    lazyloadGroups(route)
                }
            },
            type: 'GET'

        })
    }
})();
