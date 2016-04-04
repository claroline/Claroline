/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
                    var search = $('#search-mode').attr('search');

                    if (search === '') {
                        route = url;
                    }
                    else {
                        route = Routing.generate(
                            'claro_workspaces_list_registration_pager_search',
                            {'search': search, 'page': page}
                        );
                    }
                }
                else {
                    route = Routing.generate(
                        'claro_workspace_list_registration_pager',
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
