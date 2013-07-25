(function () {
    'use strict';

    var twigUserId = document.getElementById('twig-self-registration-user-id').getAttribute('data-user-id');

    $('.register-user-to-workspace').click(function () {
        var workspaceId = $(this).attr('data-workspace-id');
        var workspaceName = $(this).attr('data-workspace-name');
        var workspaceCode = $(this).attr('data-workspace-code');
        $('#registration-confirm-message').html(workspaceName + ' [' + workspaceCode + ']');
        $('#confirm-registration-validation-box').modal('show');

        $('#registration-confirm-ok').click(function () {
            var route = Routing.generate(
                'claro_workspace_add_user',
                {'workspaceId': workspaceId, 'userId': twigUserId}
            );
            $.ajax({
                url: route,
                type: 'POST',
                success: function () {
                    window.location = Routing.generate('claro_list_workspaces_with_self_registration');
                }
            });
            $('#confirm-registration-validation-box').modal('hide');
            $('#registration-confirm-message').empty();
        });
    });

    $('.unregister-user-to-workspace').click(function () {
        var workspaceId = $(this).attr('data-workspace-id');
        var workspaceName = $(this).attr('data-workspace-name');
        var workspaceCode = $(this).attr('data-workspace-code');
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
                    window.location = Routing.generate('claro_list_workspaces_with_self_registration');
                }
            });
            $('#confirm-unregistration-validation-box').modal('hide');
            $('#unregistration-confirm-message').empty();
        });
    });
})();