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

    var translator = window.Translator;

    $('#check-notifications').on('change', function () {
        var val = ($('#check-notifications').is(':checked')) ? 1: 0;
        $.ajax({
            type: 'POST',
            url: Routing.generate('claro_message_notification', {'isNotified': val}),
            success: function () {
                var translationKey = (val === 0) ? 'notification_deactivated': 'notification_activated';
                var toAppend = '<div class="alert alert-info">' +
                    '<a class="close" data-dismiss="alert" href="#" aria-hidden="true">&times;</a>' +
                    translator.trans(translationKey, {}, 'platform') +
                    '</div>';
                $('#flashbox').html(toAppend);
            }
        });
    });
})();
