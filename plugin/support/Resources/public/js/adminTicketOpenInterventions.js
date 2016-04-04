(function () {
    'use strict';
    
    $('#interventions-box').on('click', '.delete-intervention-btn', function (e) {
        var interventionId = $(this).data('intervention-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_admin_ticket_intervention_delete',
                {'intervention': interventionId}
            ),
            reloadPage,
            interventionId,
            Translator.trans('intervention_deletion_confirm_message', {}, 'support'),
            Translator.trans('intervention_deletion', {}, 'support')
        );
    });

    var reloadPage = function () {
        window.location.reload();
    };
})();