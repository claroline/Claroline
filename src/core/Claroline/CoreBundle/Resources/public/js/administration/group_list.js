(function () {
    $('html, body').animate({scrollTop: 0}, 0);
    $('#loading').hide();

    var route = Routing.generate('claro_admin_paginated_group_list', {
        'offset' : 0,
        'format': 'html'
    });

    ClaroUtils.sendRequest(route, function(users){
        $('#group-table-body').append($(users));
    })

    var loading = false;

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false){
            loading = true;
            $('#loading').show();
            var route = Routing.generate('claro_admin_paginated_group_list', {
                'offset' : $('.row-group').length,
                'format': 'html'
            });
            ClaroUtils.sendRequest(route, function(users){
                $('#group-table-body').append($(users));
                loading = false;
                $('#loading').hide();
            })
        }
    });

    $('.link-delete-group').live('click', function(e){
        e.preventDefault();
        var route = $(this).attr('href');
        var element = $(this).parent().parent();
        ClaroUtils.sendRequest(
            route,
            function(){
                element.remove();
            },
            undefined,
            'DELETE'
        )
    })
})();