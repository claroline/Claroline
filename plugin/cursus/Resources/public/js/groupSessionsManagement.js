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
    
    $('.delete-session-group-btn').on('click', function () {
        var sessionGroupId = $(this).data('session-group-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_session_unregister_group',
                {'sessionGroup': sessionGroupId}
            ),
            removeSessionRow,
            sessionGroupId,
            Translator.trans('unregister_group_from_session_message', {}, 'cursus'),
            Translator.trans('unregister_group_from_session', {}, 'cursus')
        );
    });
    
    var removeSessionRow = function (event, sessionGroupId) {
        $('#session-row-' + sessionGroupId).remove();
    };
})();