(function () {
    'use strict';

    function emptyFlashBag() {
        $('#custom-flashbag-div').addClass('hide');
        $('#custom-flashbag-msg-div').empty();
    }

    function addFlashBagMessage() {
        var msgA = Translator.get('platform:cannot_unsubscribe_from_workspace');
        msgA += ' "' + workspaceName + ' [' + workspaceCode + ']".';
        var msgB = Translator.get('platform:cannot_delete_unique_manager');
        var msg = '<p>' + msgA + '</p><p>' + msgB + '</p>';
        $('#custom-flashbag-msg-div').append(msg);
        $('#custom-flashbag-div').removeClass('hide');
    }

    var twigUserId = document.getElementById('twig-self-registration-user-id').getAttribute('data-user-id');
    var workspaceId;
    var workspaceName;
    var workspaceCode;
    var currentElement;

    $('.unregister-btn').click(function () {
        currentElement = $(this);
        workspaceId = $(this).attr('data-workspace-id');
        workspaceName = $(this).attr('data-workspace-name');
        workspaceCode = $(this).attr('data-workspace-code');
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
                        emptyFlashBag();
                        addFlashBagMessage();
                    }
                }
            }
        });
        $('#confirm-unregistration-validation-box').modal('hide');
        $('#unregistration-confirm-message').empty();
    });

    $('#flashbag-close-button').click(function () {
        emptyFlashBag();
    });
})();