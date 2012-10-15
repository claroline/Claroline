(function(){
    var nbPages = document.getElementById('twig-attributes').getAttribute('data-nbPages');
    var limit = document.getElementById('twig-attributes').getAttribute('data-limit');
    var subjectInstanceId = document.getElementById('twig-attributes').getAttribute('data-subjectInstanceId');
    var activePage = 1;
    var pager = activePageMessages(activePage);

    function activePageMessages(page){

        if (pager !== undefined){
            pager.remove();
        }

        pager = ClaroUtils.renderPager(nbPages, page, 'message', $('#messages_table'));
        setPagerActions();
        var offset = (page-1)*limit;
        var route = Routing.generate('claro_forum_messages', {'subjectInstanceId': subjectInstanceId, 'offset': offset});
        ClaroUtils.sendRequest(
            route,
            function(data){
                $('#table-message-body').empty();
                $('#table-message-body').append(data);
            },
            undefined,
            'GET'
        );

        return pager;
    }

    function setPagerActions(){
        $('.message-paginator-item').on('click', function(e){
            activePage = e.target.innerHTML;
            activePageMessages(activePage);
        });

        $('.message-paginator-next-item').on('click', function(e){
            activePage++;
            activePageMessages(activePage);
        })

        $('.message-paginator-prev-item').on('click', function(e){
            activePage--;
            activePageMessages(activePage);
        })
    }
})();