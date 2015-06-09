(function () {
    'use strict';
    
    $('#support-tool').on('click', '.delete-ticket-btn', function () {
        var ticketId = $(this).data('ticket-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_ticket_delete',
                {'ticket': ticketId}
            ),
            removeTicketRow,
            ticketId,
            Translator.trans('ticket_deletion_confirm_message', {}, 'support'),
            Translator.trans('ticket_deletion', {}, 'support')
        );
    });
    
    var removeTicketRow = function (event, ticketId) {
        $('#row-ticket-' + ticketId).remove();
    };
})();