(function () {
    'use strict';
    
    $('#ticket-comment-form-box').on('click', '#add-comment-btn', function (e) {
        e.stopImmediatePropagation();
        e.preventDefault();

        var form = document.getElementById('comment-create-form');
        var action = form.getAttribute('action');
        var formData = new FormData(form);

        $.ajax({
            url: action,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data, textStatus, jqXHR) {
                switch (jqXHR.status) {
                    case 201:
                        reloadCommentsPage();
                        break;
                    default:
                        $('#ticket-comment-form-box').html(data);
                }
            }
        });
    });
    
    $('#comments-box').on('click', '.edit-comment-btn', function (e) {
        var commentId = $(this).data('comment-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'formalibre_admin_ticket_comment_edit_form',
                {'comment': commentId}
            ),
            reloadCommentsPage,
            function() {}
        );
    });
    
    $('#comments-box').on('click', '.delete-comment-btn', function (e) {
        var commentId = $(this).data('comment-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'formalibre_admin_ticket_comment_delete',
                {'comment': commentId}
            ),
            removeCommentRow,
            commentId,
            Translator.trans('comment_deletion_confirm_message', {}, 'support'),
            Translator.trans('comment_deletion', {}, 'support')
        );
    });

    var reloadCommentsPage = function () {
        $('#comment_form_content').html('');
        $('#comment_edit_form_content').html('');
        window.location.reload();
    };
    
    var removeCommentRow = function (event, commentId) {
        $('#row-comment-' + commentId).remove();
    };
})();