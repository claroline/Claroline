(function () {
    $(function() {
        var stackedRequests = 0;
        $( "#sortable" ).sortable({
            update: function( event, ui){
                stackedRequests++;
                $('.please-wait').show();
                Claroline.Utilities.ajax({
                    url: Routing.generate('claro_activity_set_sequence', {
                        'activityId': document.getElementById('twig-attributes').getAttribute('data-activity-id')
                        }),
                    data: {
                        ids: $("#sortable").sortable("toArray")
                        },
                    success: function(){
                        stackedRequests--;
                        if (stackedRequests === 0) {
                            $('.please-wait').hide();
                        }
                    }
                });
            }
        });
        $( "#sortable" ).disableSelection();
    });
})();
