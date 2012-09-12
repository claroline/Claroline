//window.onload(function(){alert("loading...")});
(function () {
    $('html, body').animate({scrollTop: 0}, 1000);
    $('#loading').hide();

    var route = Routing.generate('claro_admin_paginated_user_list', {
        'page' : 1,
        'format': 'html'
    });

    ClaroUtils.sendRequest(route, function(users){
        $('#user-table-body').append($(users));
    })

    var page = 2;
    var loading = false;

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false){
            loading = true;
            $('#loading').show();
            var route = Routing.generate('claro_admin_paginated_user_list', {
                'page' : page,
                'format': 'html'
            });
            page++;
            ClaroUtils.sendRequest(route, function(users){
                $('#user-table-body').append($(users));
                loading = false;
                $('#loading').hide();
            })
        }
    });
})();