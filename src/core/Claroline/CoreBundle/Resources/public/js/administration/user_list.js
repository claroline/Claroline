(function () {
    var activePagerItem = 1;

    $('.user-paginator-item').on('click', function(e){
        activePagerItem = e.target.innerHTML;
        renderUsers();
    });

    $('.user-paginator-next-item').on('click', function(e){
        activePagerItem++;
        renderUsers();
    })

    $('.user-paginator-prev-item').on('click', function(e){
        activePagerItem--;
        renderUsers();
    })

    function renderUsers() {
        var route = Routing.generate('claro_admin_paginated_user_list', {'page' : activePagerItem, 'format': 'html'});
        ClaroUtils.sendRequest(route, function(users){
            $('#user-table').remove();
            $('#user-list-block').after(users);
        })
    }
})();