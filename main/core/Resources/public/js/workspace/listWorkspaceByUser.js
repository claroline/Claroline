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

    // Click on favourite button of a workspace
    $('.favourite-workspace-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var favouriteBtn = $(this);
        var workspaceId = parseInt(favouriteBtn.attr('workspace-id'));
        var favouriteIconClass = '.favourite-workspace-icon-' + workspaceId;

        $.ajax({
            url: Routing.generate(
                'claro_workspace_update_favourite',
                {'workspaceId': workspaceId}
            ),
            type: 'POST',
            success: function (data) {
                if (data === 'added') {
                    $(favouriteIconClass).each(function () {
                        $(this).removeClass('fa-star-o');
                        $(this).addClass('fa-star');
                    });
                } else {
                    $(favouriteIconClass).each(function () {
                        $(this).removeClass('fa-star');
                        $(this).addClass('fa-star-o');
                    });
                }
            },
            error: function () {}
        });
    });
})();
