//window.onload(function(){alert("loading...")});
(function () {
    var loading = false;
    var stop = false;
    var mode = 0; //0 = standard || 1 = search
    var groupId = document.getElementById('twig-attributes').getAttribute('data-group-id');

    $('html, body').animate({
        scrollTop: 0
    }, 0);
    $('#loading').hide();

    var standardRoute = function(){
        return Routing.generate('claro_admin_paginated_group_user_list', {
            'offset' : $('.row-user').length,
            'groupId': groupId
        });
    }

    //fake one and wrong url.
    var searchRoute = function(){
        return Routing.generate('claro_admin_search_groupless_users', {
            'offset' : $('.row-user').length,
            'groupId': groupId,
            'search':  document.getElementById('search-user-txt').value
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

    function lazyloadUsers(route){
        loading = true;
        $('#loading').show();
        ClaroUtils.sendRequest(
            route(),
            function(users){
                $('#user-table-body').append(Twig.render(user_list_short, {
                    'users': users
                }));
                loading = false;
                $('#loading').hide();
                if (users.length == 0) {
                    stop = true;
                }
            },
            function(){
                if($(window).height() >= $(document).height() && stop == false){
                    lazyloadUsers(route)
                }
            }
        )
    }
})();