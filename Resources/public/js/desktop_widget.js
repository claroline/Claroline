(function () {
    'use strict';

    function getPage(tab)
    {
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

    function initEvents()
    {
        $('body').on('click', '#desktop-widget-pager > .pagination > ul > li > a', function (event) {
            event.preventDefault();
            event.stopPropagation();
            var element = event.currentTarget;
            var url = $(element).attr('href');

            if (url !== '#') {
                var urlTab = url.split('/');
                var page = getPage(urlTab);

                var route = Routing.generate(
                    'claro_desktop_announcements_pager',
                    {'page': page}
                );

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