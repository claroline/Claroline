(function () {
    $(function() {
        $( "#sortable" ).sortable({
            change: function( event, ui){
                $('.alert-message').html('Changes were not saved');
            }
        });
        $( "#sortable" ).disableSelection();
    });

    $('#validate-button').click(function(){
      Claroline.Utilities.ajax({
          url: Routing.generate('claro_activity_set_sequence', {'activityId': document.getElementById('twig-attributes').getAttribute('data-activity-id')}),
          data: {ids: $("#sortable").sortable("toArray")},
          success: function(){
               $('.alert-message').html('');
          }
       });
    });
})();
