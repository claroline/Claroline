(function () {
    'use strict';
    $('.link-delete-workspace').click(function () {
        var route = Routing.generate('claro_workspace_delete', {'workspaceId': $(this).attr('data-workspace-id')});
        var row = $(this).parent();
        $.ajax({
            url: route,
            success: function () {
                row.remove();
            },
            type: 'DELETE'
        });
    });
})();
