(function () {
    'use strict';

    $('#add-tag-button').click(function (e) {
        e.preventDefault();
        $('#modal-tag-input').val('');
        $('#add-tag-validation-box').modal('show');
    });

    $('#add-tag-confirm-ok').click(function () {
        var tagName = $.trim($('#modal-tag-input').val());
        $.ajax({
            url: Routing.generate(
                'claro_create_admin_workspace_tag',
                {'tagName': tagName}
            ),
            type: 'POST',
            success: function () {
                $('#add-tag-validation-box').modal('hide');
                location.reload();
            }
        });
    });
})();