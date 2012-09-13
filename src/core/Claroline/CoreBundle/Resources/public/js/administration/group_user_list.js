//window.onload(function(){alert("loading...")});
(function () {
    $('html, body').animate({scrollTop: 0}, 0);
    $('#loading').hide();

     var groupId = document.getElementById('twig-attributes').getAttribute('data-group-id');
    var route = Routing.generate('claro_admin_paginated_group_user_list', {
        'page' : 1,
        'groupId': groupId
    });

    ClaroUtils.sendRequest(route, function(users){
        $('#user-table-body').append(Twig.render(group_user_list, {'users': users}));
    })

    var page = 2;
    var loading = false;

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false){
            loading = true;
            $('#loading').show();
            var route = Routing.generate('claro_admin_paginated_group_user_list', {
                'page' : page,
                'groupId': groupId
            });
            ClaroUtils.sendRequest(route, function(users){
                page++;
               $('#user-table-body').append(Twig.render(group_user_list, {'users': users}));
                loading = false;
                $('#loading').hide();
            })
        }
    });

    $('.link_delete').live('click', function(e){
        e.preventDefault();
        var userId = $(this).attr('data-user-id');
        var route = Routing.generate('claro_admin_delete_user_from_group', {'groupId': groupId, 'userId': userId});
        var element = $(this).parent().parent();

        ClaroUtils.sendRequest(
            route,
            function(){
                element.remove();
            },
            undefined,
            'DELETE'
        )
    });
})();