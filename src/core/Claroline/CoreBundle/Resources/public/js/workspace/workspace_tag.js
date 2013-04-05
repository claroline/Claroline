(function () {
    'use strict';

    var twigUserId = document.getElementById('twig-add-tag-user-id').getAttribute('data-user-id');

    $('.add-tag-button').popover();
    $('.add-tag-button').popover({ trigger: "hover" });

    $('.add-tag-button').click(function () {
        var twigWorkspaceId = $(this).attr('data-workspace-id');
        $('#modal-tag-input').val('');
        $('#add-tag-validation-box').modal('show');

        $('#add-tag-confirm-ok').click(function () {
            var tagName = $.trim($('#modal-tag-input').val());
            $.ajax({
                url: Routing.generate(
                    'claro_workspace_tag_add',
                    {
                        'userId': twigUserId,
                        'workspaceId': twigWorkspaceId,
                        'tagName': tagName
                    }
                ),
                type: 'POST',
                success: function () {
                    $('#add-tag-validation-box').modal('hide');
                    location.reload();
                }
            });
        });
    });
})();