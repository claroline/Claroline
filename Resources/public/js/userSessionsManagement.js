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
    
    $('.delete-session-user-btn').on('click', function () {
        var sessionUserId = $(this).data('session-user-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_session_unregister_user',
                {'sessionUser': sessionUserId}
            ),
            removeSessionRow,
            sessionUserId,
            Translator.trans('unregister_user_from_session_message', {}, 'cursus'),
            Translator.trans('unregister_user_from_session', {}, 'cursus')
        );
    });
    
    var removeSessionRow = function (event, sessionUserId) {
        $('#session-row-' + sessionUserId).remove();
    };
})();