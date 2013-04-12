(function () {
    'use strict';

    var twigUserId = document.getElementById('twig-add-tag-user-id').getAttribute('data-user-id');
    var twigWorkspaceId;

    $('.add-tag-button').popover();
    $('.add-tag-button').popover({ trigger: 'hover' });
    $('.add-admin-tag-button').popover();
    $('.add-admin-tag-button').popover({ trigger: 'hover' });

    $('.add-tag-button').click(function () {
        twigWorkspaceId = $(this).attr('data-workspace-id');
        $('#modal-tag-input').val('');
        $('#add-tag-validation-box').modal('show');
    });

    $('#add-tag-confirm-ok').click(function () {
        var tagName = $.trim($('#modal-tag-input').val());
        $.ajax({
            url: Routing.generate(
                'claro_workspace_tag_add',
                {'userId': twigUserId, 'workspaceId': twigWorkspaceId, 'tagName': tagName}
            ),
            type: 'POST',
            success: function () {
                $('#add-tag-validation-box').modal('hide');
                location.reload();
            }
        });
    });

    $('#add-admin-tag-confirm-ok').click(function () {
        var tagName = $.trim($('#modal-tag-input').val());
        $.ajax({
            url: Routing.generate(
                'claro_workspace_admin_tag_add',
                {'workspaceId': twigWorkspaceId, 'tagName': tagName}
            ),
            type: 'POST',
            success: function () {
                $('#add-tag-validation-box').modal('hide');
                location.reload();
            }
        });
    });

    $('.remove-tag-button').click(function () {
        var twigWorkspaceId = $(this).attr('data-workspace-id');
        var twigTagId = $(this).attr('data-tag-id');
        var span = $(this).parent();

        $.ajax({
            url: Routing.generate(
                'claro_workspace_tag_remove',
                {'userId': twigUserId, 'workspaceId': twigWorkspaceId, 'workspaceTagId': twigTagId}
            ),
            type: 'DELETE',
            success: function () {
                span.remove();
            }
        });
    });

    $('.remove-admin-tag-button').click(function () {
        var twigWorkspaceId = $(this).attr('data-workspace-id');
        var twigTagId = $(this).attr('data-tag-id');
        var span = $(this).parent();

        $.ajax({
            url: Routing.generate(
                'claro_workspace_admin_tag_remove',
                {'workspaceId': twigWorkspaceId, 'workspaceTagId': twigTagId}
            ),
            type: 'DELETE',
            success: function () {
                span.remove();
            }
        });
    });
})();