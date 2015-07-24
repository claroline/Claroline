(function () {
    'use strict';
    
    $('#status-management-body').on('click', '#create-status-btn', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('formalibre_admin_support_status_create_form'),
            reloadPage,
            function() {}
        );
    });
    
    $('#status-management-body').on('click', '.edit-status-btn', function () {
        var statusId = $(this).data('status-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibre_admin_support_status_edit_form',
                {'status': statusId}
            ),
            reloadPage,
            function() {}
        );
    });
    
    $('#status-management-body').on('click', '.delete-status-btn', function () {
        var statusId = $(this).data('status-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_admin_support_status_delete',
                {'status': statusId}
            ),
            reloadPage,
            statusId,
            Translator.trans('support_status_deletion_confirm_message', {}, 'support'),
            Translator.trans('support_status_deletion', {}, 'support')
        );
    });
    
    $('#status-elements-list').sortable({
        items: '.movable-status',
        cursor: 'move'
    });
    
    $('#status-elements-list').on('sortupdate', function (event, ui) {

        if (this === ui.item.parents('#status-elements-list')[0]) {
            var statusId = $(ui.item).data('status-id');
            var nextStatusId = -1;
            var nextElement = $(ui.item).next();
            
            if (nextElement !== undefined && nextElement.hasClass('movable-status')) {
                nextStatusId = nextElement.data('status-id');
            }
            
            $.ajax({
                url: Routing.generate(
                    'formalibre_admin_support_status_reorder',
                    {'status': statusId, 'nextStatusId': nextStatusId}
                ),
                type: 'POST'
            });
        }
    });
    
    var reloadPage = function () {
        window.location.reload();
    };
})();