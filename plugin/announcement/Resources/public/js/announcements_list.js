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
    var sendMailToUsers = function (datas) {
        if (datas) {
            var usersIds = [];
            var url = Routing.generate('claro_announcement_send_mail', {announcement: announcementId});
            var parameters = {};
            datas.forEach(d => {usersIds.push(d['id'])});
            parameters.usersIds = usersIds;
            url += '?' + $.param(parameters);
            $.ajax({
                url: url,
                type: 'POST',
                success: function () {
                    var title = Translator.trans('announcement_sent', {}, 'announcement');
                    var body = '<h4>' + Translator.trans('receiver', {}, 'platform') + '</h4><ul>';
                    datas.forEach(d => {
                        body += '<li>' + d['firstName'] + ' ' + d['lastName'] + '</li>';
                    });
                    body += '</ul>';
                    var footer = '<button class="btn btn-default pull-right" data-dismiss="modal">' +
                        Translator.trans('close', {}, 'platform') +
                        '</button>';
                    window.Claroline.Modal.simpleContainer(title, body, footer);
                }
            });
        }
    };

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

    $('.announcement-send-button').click(function () {
        announcementId = $(this).data('announcement-id');
        var workspaceId = $(this).data('workspace-id');
        var userPicker = new UserPicker();
        var options = {
            'picker_name': 'send_announcement_users_picker',
            'picker_title': Translator.trans('send_mail_user_picker_title', {}, 'announcement'),
            'multiple': true,
            'forced_workspaces': [workspaceId],
            'return_datas': true
        }

        userPicker.configure(options, sendMailToUsers);
        userPicker.open();
    });
})();