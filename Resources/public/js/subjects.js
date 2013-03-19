(function () {
    'use strict';

    var nbPages = document.getElementById('twig-attributes').getAttribute('data-nbPages');
    var limit = document.getElementById('twig-attributes').getAttribute('data-limit');
    var forumId = document.getElementById('twig-attributes').getAttribute('data-forumId');
    var activePage = 1;
    var pager = activePageSubjects(activePage);

    function activePageSubjects(page) {

        if (pager !== undefined) {
            pager.remove();
        }

        pager = Claroline.Utilities.renderPager(nbPages, page, 'subject', $('#subjects_table'));
        setPagerActions();
        var offset = (page - 1) * limit;
        var route = Routing.generate('claro_forum_subjects', {'forumId': forumId, 'offset': offset});
        $.ajax({
            url: route,
            success: function (data) {
                $('#table-subjects-body').empty();
                $('#table-subjects-body').append(data);
            },
            type: 'GET'
        });

        return pager;
    }

    function setPagerActions() {
        $('.subject-paginator-item').on('click', function (e) {
            activePage = e.target.innerHTML;
            activePageSubjects(activePage);
        });

        $('.subject-paginator-next-item').on('click', function () {
            activePage++;
            activePageSubjects(activePage);
        });

        $('.subject-paginator-prev-item').on('click', function () {
            activePage--;
            activePageSubjects(activePage);
        });
    }
})();