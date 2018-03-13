/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    var twigWorkspaceId = document.getElementById('twig-workspace-id').getAttribute('data-workspaceId');

    $('.workspace-delete-confirmation').click(function () {
        $('#delete-ws-validation-box').modal('show');
    });

    $('#delete-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate('claro_workspace_delete', {'workspaceId': twigWorkspaceId}),
            type: 'DELETE',
            success: function () {
                window.location = Routing.generate('claro_desktop_open_tool', {'toolName': 'home'});
            },
            error: function (xhr) {
                if (xhr.status === 403) {
                  window.location.reload();
                }
            }
        });
    });
})();