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
    
    $('#course-session-create-btn').on('click', function () {
        var courseId = $(this).data('course-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_cursus_course_session_create_form',
                {'course': courseId}
            ),
            refreshPage,
            function() {}
        );
    });

    var refreshPage = function () {
        window.location.reload();
    }
})();