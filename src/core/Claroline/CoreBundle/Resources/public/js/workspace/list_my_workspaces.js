(function () {
    'use strict';

    var twigUserId = document.getElementById('twig-self-registration-user-id').getAttribute('data-user-id');

    $('.unregister-user-to-workspace').click(function () {
        var workspaceId = $(this).attr('data-workspace-id');
        var workspaceName = $(this).attr('data-workspace-name');
        var workspaceCode = $(this).attr('data-workspace-code');
        var elementClassName = '.row-workspace-id-' + workspaceId;
        $('#unregistration-confirm-message').html(workspaceName + ' [' + workspaceCode + ']');
        $('#confirm-unregistration-validation-box').modal('show');

        $('#unregistration-confirm-ok').click(function () {
            var route = Routing.generate(
                'claro_workspace_delete_user',
                {'workspaceId': workspaceId, 'userId': twigUserId}
            );
            $.ajax({
                url: route,
                type: 'DELETE',
                success: function () {
                    $(elementClassName).each(function () {
                        $(this).remove();
                    });
                }
            });
            $('#confirm-unregistration-validation-box').modal('hide');
            $('#unregistration-confirm-message').empty();
        });
    });
})();