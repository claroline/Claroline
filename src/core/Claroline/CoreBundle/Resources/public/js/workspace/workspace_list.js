(function () {
    'use strict';

    function getTagId(tab) {
        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'tag') {
                return tab[i + 1];
            }
        }

        return -1;
    }

    function getPage(tab) {
        var page = 1;

        for (var i = 0; i < tab.length; i++) {
            if (tab[i] === 'page') {
                if (typeof(tab[i + 1]) !== 'undefined') {
                    page = tab[i + 1];
                }
                break;
            }
        }

        return page;
    }

    function initEvents() {
        $('#workspace-list-div').on('click', '.pagination > ul > li > a', function (event) {
            event.preventDefault();
            event.stopPropagation();

            var element = event.currentTarget;
            var url = $(element).attr('href');
            var route;

            if (url !== '#') {
                var urlTab = url.split('/');
                var tagId = getTagId(urlTab);
                var page = getPage(urlTab);

                if (tagId === -1) {
                    route = Routing.generate('claro_all_workspaces_list_pager', {'page': page});
                }
                else {
                    route = Routing.generate(
                        'claro_workspace_list_pager',
                        {'workspaceTagId': tagId, 'page': page}
                    );
                }
                $.ajax({
                    url: route,
                    success: function (result) {
                        var source = $(element).parent().parent().parent().parent();
                        $(source).children().remove();
                        $(source).append(result);
                    },
                    type: 'GET'
                });
            }
        });
    }

    initEvents();
})();