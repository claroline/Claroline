(function () {
    $('html, body').animate({
        scrollTop: 0
    }, 0);

    var twigWorkspaceId = document.getElementById('twig-attributes').getAttribute('data-workspaceId');
    var loading = false;
    var stop = false;
    var mode = 0; //0 = standard || 1 = search

    $('.btn-save-users').attr('disabled', 'disabled');

    $('.checkbox-user-name').live('change', function(){
        if ($('.checkbox-user-name:checked').length){
           $('.btn-save-users').removeAttr('disabled');
        } else {
           $('.btn-save-users').attr('disabled', 'disabled');
        }
    })

    var standardRoute = function(){
        return Routing.generate('claro_workspace_unregistered_users_paginated', {
            'workspaceId': twigWorkspaceId,
            'offset': $('.row-user').length
        });
    }

    var searchRoute = function(){
        return Routing.generate('claro_workspace_search_unregistered_users', {
                'search': document.getElementById('search-user-txt').value,
                'workspaceId': twigWorkspaceId,
                'offset': $('.row-user').length
            })
    }

    lazyloadUsers(standardRoute);

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false && stop === false){
            if(mode == 0){
                lazyloadUsers(standardRoute);
            } else {
                lazyloadUsers(searchRoute);
            }
        }
    });

    $('#search-button').click(function(){
        $('.checkbox-user-name').remove();
        $('#user-table-checkboxes-body').empty();
        stop = false;
        if (document.getElementById('search-user-txt').value != ''){
            mode = 1;
            lazyloadUsers(searchRoute);
        } else {
            mode = 0;
            lazyloadUsers(standardRoute);
        }
    });

    $('.btn-save-users').on('click', function(event){
        var parameters = {};
        var i = 0;
        var array = new Array();
        $('.checkbox-user-name:checked').each(function(index, element){
            array[i] = element.value;
            i++;
        })
        parameters.userIds = array;
        var route = Routing.generate('claro_workspace_multiadd_user', {'workspaceId': twigWorkspaceId});
        route+='?'+$.param(parameters);
        Claroline.Utilities.ajax({
            url: route,
            success: function(users){alert(Twig.render(add_user_confirm, {'nbUsers': users.length}))},
            type: 'PUT'
        })
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
            +'<td align="center">'+JSONString[i].username+'</td>'
            +'<td align="center">'+JSONString[i].lastname+'</td>'
            +'<td align="center">'+JSONString[i].firstname+'</td>'
            +'<td align="center"><input class="checkbox-user-name" id="checkbox-user-'+JSONString[i].id+'" type="checkbox" value="'+JSONString[i].id+'" id="checkbox-user-'+JSONString[i].id+'"></input></td>'
            +'</tr>';
            $('#user-table-checkboxes-body').append(list);
            i++;
        }
    }

    function lazyloadUsers(route) {
        loading = true;
        $('#user-loading').show();
        Claroline.Utilities.ajax({
            url: route(),
            success: function(users){
                createUsersChkBoxes(users);
                loading = false;
                $('#user-loading').hide();
                if(users.length == 0) {
                    stop = true;
                }
            },
            complete: function(){
                if($(window).height() >= $(document).height() && stop == false){
                    lazyloadUsers(route)
                }
            },
            type: 'GET'
        });
    }
})();