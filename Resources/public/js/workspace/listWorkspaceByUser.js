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
                        $(this).removeClass('icon-star-empty');
                        $(this).addClass('icon-star');
                    });
                } else {
                    $(favouriteIconClass).each(function () {
                        $(this).removeClass('icon-star');
                        $(this).addClass('icon-star-empty');
                    });
                }
            },
            error: function () {}
        });
    });
})();