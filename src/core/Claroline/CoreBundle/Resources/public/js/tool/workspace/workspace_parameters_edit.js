(function () {
    'use strict';

    var twigWorkspaceId = document.getElementById('twig-workspace-id').getAttribute('data-workspaceId');

    $('.workspace-delete-confirmation').click(function () {
        $('#delete-ws-validation-box').modal('show');
        $('#delete-confirm-ok').click(function () {
            $.ajax({
                url: Routing.generate('claro_workspace_delete', {'workspaceId': twigWorkspaceId}),
                type: 'DELETE',
                success: function () {
                    window.location = Routing.generate('claro_desktop_open_tool', {'toolName': 'home'});
                }
            });
        });
    });
})();