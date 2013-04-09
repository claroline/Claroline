(function () {
    'use strict';

    $('.link-delete-workspace').click(function () {
        var workspaceId = $(this).attr('data-workspace-id');
        var route = Routing.generate('claro_workspace_delete', {'workspaceId': workspaceId});
        var row = $(this).parent();
        $.ajax({
            url: route,
            success: function () {
                row.remove();
                var rowClass = '.row-workspace-id-' + workspaceId;
                $(rowClass).remove();
            },
            type: 'DELETE'
        });
    });
})();
