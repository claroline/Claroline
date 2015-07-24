(function () {
    'use strict';
    
    $('#types-management-body').on('click', '#create-type-btn', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('formalibre_admin_support_type_create_form'),
            reloadPage,
            function() {}
        );
    });
    
    $('#types-management-body').on('click', '.edit-type-btn', function () {
        var typeId = $(this).data('type-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibre_admin_support_type_edit_form',
                {'type': typeId}
            ),
            reloadPage,
            function() {}
        );
    });
    
    $('#types-management-body').on('click', '.delete-type-btn', function () {
        var typeId = $(this).data('type-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_admin_support_type_delete',
                {'type': typeId}
            ),
            reloadPage,
            typeId,
            Translator.trans('support_type_deletion_confirm_message', {}, 'support'),
            Translator.trans('support_type_deletion', {}, 'support')
        );
    });
    
    var reloadPage = function () {
        window.location.reload();
    };
})();