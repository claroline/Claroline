(function () {
    'use strict';
    $(':submit').on('click', function (e) {
        e.preventDefault();
        var formAction = $(e.currentTarget.parentElement).attr('action');
        var form = document.getElementById('form-resource-creation-rights');
        var formData = new FormData(form);
        $.ajax({
            url: formAction,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function () {
                window.location.href = Routing.generate('claro_workspace_open_tool',
                {'toolName': 'parameters', 'workspaceId': $('#data').attr('data-workspace-id') });
            }
        });
    });
})();