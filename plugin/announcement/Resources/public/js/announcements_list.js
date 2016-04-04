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