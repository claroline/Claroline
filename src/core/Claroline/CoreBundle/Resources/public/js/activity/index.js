(function () {
    $(function() {
        $( "#sortable" ).sortable({
            update: function( event, ui){
                Claroline.Utilities.ajax({
                    url: Routing.generate('claro_activity_set_sequence', {
                        'activityId': document.getElementById('twig-attributes').getAttribute('data-activity-id')
                        }),
                    data: {
                        ids: $("#sortable").sortable("toArray")
                        },
                    success: function(){
                        $('.alert-message').html('');
                    }

                });
            }
        });
        $( "#sortable" ).disableSelection();
    });
})();
