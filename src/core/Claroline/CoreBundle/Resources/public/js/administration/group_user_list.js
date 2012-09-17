//window.onload(function(){alert("loading...")});
(function () {
    $('html, body').animate({scrollTop: 0}, 0);
    $('#loading').hide();


     var groupId = document.getElementById('twig-attributes').getAttribute('data-group-id');
    var route = Routing.generate('claro_admin_paginated_group_user_list', {
        'offset' : 0,
        'groupId': groupId
    });

    ClaroUtils.sendRequest(route, function(users){
        $('#user-table-body').append(Twig.render(user_list, {'users': users}));
    })

    var loading = false;

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false){
            loading = true;
            $('#loading').show();
            var route = Routing.generate('claro_admin_paginated_group_user_list', {
                'offset' : $('.row-user', $(users)),
                'groupId': groupId
            });
            ClaroUtils.sendRequest(route, function(users){
               $('#user-table-body').append(Twig.render(user_list, {'users': users}));
                loading = false;
                $('#loading').hide();
            })
        }
    });

    $('.link-delete-user').live('click', function(e){
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