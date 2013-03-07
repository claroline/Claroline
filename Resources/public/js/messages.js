(function () {
    'use strict';

    function activePageMessages(page) {

        if (pager !== undefined) {
            pager.remove();
        }

        pager = Claroline.Utilities.renderPager(nbPages, page, 'message', $('#messages_table'));
        setPagerActions();
        var offset = (page - 1) * limit;
        var route = Routing.generate('claro_forum_messages', {'subjectId': subjectId, 'offset': offset});
        Claroline.Utilities.ajax({
            url: route,
            success: function (data) {
                $('#table-message-body').empty();
                $('#table-message-body').append(data);
            },
            type: 'GET'
        });

        return pager;
    }

    function setPagerActions() {
        $('.message-paginator-item').on('click', function (e) {
            activePage = e.target.innerHTML;
            activePageMessages(activePage);
        });

        $('.message-paginator-next-item').on('click', function () {
            activePage++;
            activePageMessages(activePage);
        });

        $('.message-paginator-prev-item').on('click', function () {
            activePage--;
            activePageMessages(activePage);
        });
    }

    var nbPages = document.getElementById('twig-attributes').getAttribute('data-nbPages');
    var limit = document.getElementById('twig-attributes').getAttribute('data-limit');
    var subjectId = document.getElementById('twig-attributes').getAttribute('data-subjectId');
    var activePage = 1;
    var pager = activePageMessages(activePage);
})();