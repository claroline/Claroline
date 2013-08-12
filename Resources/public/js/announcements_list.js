(function (){
    'use strict';

    var announcementId;
    var announcementElement;

    $('.announcement-delete-button').click(function () {
        $('#delete-announcement-validation-box').modal('show');
        announcementId = $(this).attr('btn-announcement-id');
        announcementElement = $(this).parent().parent();
    });

    $('#delete-confirm-ok').click(function () {
        $.ajax({
            url: Routing.generate('claro_announcement_delete', {'announcementId': announcementId}),
            type: 'DELETE',
            success: function () {
                $('#delete-announcement-validation-box').modal('hide');
                announcementElement.remove();
            }
        });
    });
})();