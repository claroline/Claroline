(function () {
    var activePagerItem = 1;
    var pager = null;

    renderGroups(1);

    function renderGroups(activePagerItem) {
        var route = Routing.generate('claro_admin_paginated_group_list', {
            'page' : activePagerItem,
            'format': 'html'
        });
        ClaroUtils.sendRequest(route, function(groups){
            $('#group-table').remove();
            $('#group-list-block').after(groups);
            if(pager != null){
                pager.remove();
            }

            pager = ClaroUtils.renderPager(document.getElementById('twig-attributes').getAttribute('data-pages'), activePagerItem, 'group', $('#group-table'));
            setPagerActions();

        })
    }

    function setPagerActions() {
        $('.group-paginator-item').on('click', function(e){
            activePagerItem = e.target.innerHTML;
            renderGroups(activePagerItem);
        });

        $('.group-paginator-next-item').on('click', function(e){
            activePagerItem++;
            renderGroups(activePagerItem);
        })

        $('.group-paginator-prev-item').on('click', function(e){
            activePagerItem--;
            renderGroups(activePagerItem);
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