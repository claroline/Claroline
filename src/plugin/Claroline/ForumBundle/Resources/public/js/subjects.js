(function(){
    var nbPages = document.getElementById('twig-attributes').getAttribute('data-nbPages');
    var limit = document.getElementById('twig-attributes').getAttribute('data-limit');
    var forumInstanceId = document.getElementById('twig-attributes').getAttribute('data-forumInstanceId');
    var activePage = 1;
    var pager = activePageSubjects(activePage);

    function activePageSubjects(page){

        if (pager !== undefined){
            pager.remove();
        }

        pager = ClaroUtils.renderPager(nbPages, page, 'subject', $('#subjects_table'))
        setPagerActions();
        var offset = (page-1)*limit;
        var route = Routing.generate('claro_forum_subjects', {'forumInstanceId': forumInstanceId, 'offset': offset});
        ClaroUtils.sendRequest(
            route,
            function(data){
                $('#table-subjects-body').empty();
                $('#table-subjects-body').append(data);
            },
            undefined,
            'GET'
        )

            return pager;
    }

    function setPagerActions(){
        $('.subject-paginator-item').on('click', function(e){
            activePage = e.target.innerHTML;
            activePageSubjects(activePage);
        });

        $('.subject-paginator-next-item').on('click', function(e){
            activePage++;
            activePageSubjects(activePage);
        })

        $('.message-paginator-prev-item').on('click', function(e){
            subject--;
            activePageSubjects(activePage);
        })
    }

})();