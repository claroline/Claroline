(function () {
    'use strict';

    var twigUserId = document.getElementById('twig-self-registration-user-id').getAttribute('data-user-id');
    var workspaceId;
    var currentElement;

    $('.unregister-btn').click(function () {
        currentElement = $(this);
        workspaceId = $(this).attr('data-workspace-id');
        var workspaceName = $(this).attr('data-workspace-name');
        var workspaceCode = $(this).attr('data-workspace-code');
        $('#unregistration-confirm-message').html(workspaceName + ' [' + workspaceCode + ']');
        $('#confirm-unregistration-validation-box').modal('show');
    });

    $('#unregistration-confirm-ok').click(function () {
        var route = Routing.generate(
            'claro_workspace_delete_user',
            {'workspaceId': workspaceId, 'userId': twigUserId}
        );
        $.ajax({
            url: route,
            type: 'DELETE',
            statusCode: {
                204: function () {
                    currentElement.parent().remove();
                },
                200: function (data) {

                    if (data === 'cannot_delete_unique_manager') {
                        $('#custom-flashbag-div').append(Translator.get('platform:cannot_delete_unique_manager'));
                        $('#custom-flashbag-div').removeClass('hide');
                    }
                }
            }
        });
        $('#confirm-unregistration-validation-box').modal('hide');
        $('#unregistration-confirm-message').empty();
    });

    $('#flashbag-close-button').click(function () {
        $(this).parent().addClass('hide');
        $('#custom-flashbag-div').empty();
    });
})();