(function () {
    $('html, body').animate({scrollTop: 0}, 0);
    $('#loading').hide();

    var loading = false;
    addContent();

    $(window).scroll(function(){
        if  (($(window).scrollTop()+100 >= $(document).height() - $(window).height()) && loading === false){
            loading = true;
            $('#loading').show();
            var route = Routing.generate('claro_admin_paginated_user_list', {
                'offset' : $('.row-user').length,
                'format': 'html'
            });
            ClaroUtils.sendRequest(route, function(users){
                $('#user-table-body').append($(users));
                loading = false;
                $('#loading').hide();
            })
        }
    });

    $('.link-delete-user').live('click', function(e){
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
    });

    function addContent(){
        if($(window).height() >= $(document).height()){
            var route = Routing.generate('claro_admin_paginated_user_list', {
                'offset' : $('.row-user').length,
                'format': 'html'
            });

            ClaroUtils.sendRequest(route, function(users){
                $('#user-table-body').append($(users));
                addContent();
            })
        }
    }
})();