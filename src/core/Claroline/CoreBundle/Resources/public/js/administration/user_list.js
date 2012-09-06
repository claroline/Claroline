(function () {
    var activePagerItem = 1;
    var pager = null;

    renderUsers(1);

    function renderUsers(activePagerItem) {
        var route = Routing.generate('claro_admin_paginated_user_list', {
            'page' : activePagerItem,
            'format': 'html'
        });
        ClaroUtils.sendRequest(route, function(users){
            $('#user-table').remove();
            $('#user-list-block').after(users);
            if(pager != null){
                pager.remove();
            }
            pager = ClaroUtils.renderPager(document.getElementById('twig-attributes').getAttribute('data-pages'), activePagerItem, 'user', $('#user-table'));
            setPagerActions();

        })
    }

    function setPagerActions() {
        $('.user-paginator-item').on('click', function(e){
            activePagerItem = e.target.innerHTML;
            renderUsers(activePagerItem);
        });

        $('.user-paginator-next-item').on('click', function(e){
            activePagerItem++;
            renderUsers(activePagerItem);
        })

        $('.user-paginator-prev-item').on('click', function(e){
            activePagerItem--;
            renderUsers(activePagerItem);
        })
    }

    window.onresize = function(e) {
        if(pager != null) {
            pager.remove();
        }
        pager = ClaroUtils.renderPager(document.getElementById('twig-attributes').getAttribute('data-pages'), activePagerItem, 'user', $('#user-table'), 20);
        setPagerActions();
    }
})();