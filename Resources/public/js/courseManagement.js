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
    
    $('.show-users-btn').on('click', function () {
        var sessionId = $(this).data('session-id');
        
        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_session_view_management',
                {'session': sessionId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#users-area').html(datas);
                $('#users-box').removeClass('hidden');
            }
        });
    });
    
    $('#users-box').on('click', '#register-learners-btn', function () {
        var sessionId = $(this).data('session-id');
        var title = Translator.trans('register_learners_to_session', {}, 'cursus');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_session_registration_unregistered_users_list',
                {
                    'session': sessionId,
                    'userType': 0
                }
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-registration-header').html(title);
                $('#view-registration-body').html(datas);
                $('#view-registration-box').modal('show');
            }
        });
    });
    
    $('#users-box').on('click', '#register-tutors-btn', function () {
        var sessionId = $(this).data('session-id');
        var title = Translator.trans('register_tutors_to_session', {}, 'cursus');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_session_registration_unregistered_users_list',
                {
                    'session': sessionId,
                    'userType': 1
                }
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-registration-header').html(title);
                $('#view-registration-body').html(datas);
                $('#view-registration-box').modal('show');
            }
        });
    });
    
    $('#view-registration-body').on('click', 'a', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var url = $(this).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            success: function (result) {
                $('#view-registration-body').html(result);
            }
        });
    });

    $('#view-registration-body').on('click', '#search-user-btn', function () {
        var search = $('#search-user-input').val();
        var sessionId = $('#search-user-input').data('session-id');
        var userType = $('#search-user-input').data('user-type');
        var orderedBy = $('#search-user-input').data('ordered-by');
        var order = $('#search-user-input').data('order');
        var max = $('#search-user-input').data('max');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_session_registration_unregistered_users_list',
                {
                    'session': sessionId,
                    'userType': userType,
                    'search': search,
                    'orderedBy': orderedBy,
                    'order': order,
                    'max': max
                }
            ),
            type: 'GET',
            success: function (result) {
                $('#view-registration-body').html(result);
            }
        });
    });

    $('#view-registration-body').on('keypress', '#search-user-input', function (e) {
        if (e.keyCode === 13) {
        var sessionId = $(this).data('session-id');
        var userType = $(this).data('user-type');
            var orderedBy = $(this).data('ordered-by');
            var order = $(this).data('order');
            var max = $(this).data('max');
            var search = $(this).val();

            $.ajax({
                url: Routing.generate(
                    'claro_cursus_course_session_registration_unregistered_users_list',
                    {
                        'session': sessionId,
                        'userType': userType,
                        'search': search,
                        'orderedBy': orderedBy,
                        'order': order,
                        'max': max
                    }
                ),
                type: 'GET',
                success: function (result) {
                    $('#view-registration-body').html(result);
                }
            });
        }
    });
    
    $('#view-registration-box').on('click', '.register-user-btn', function () {
        var userId = $(this).data('user-id');
        var sessionId = $(this).data('session-id');
        var userType = $(this).data('user-type');
    
        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_session_register_user',
                {
                    'session': sessionId,
                    'user': userId,
                    'userType': userType
                }
            ),
            type: 'POST',
            success: function () {
                $('#registration-row-user-' + userId).remove();
            }
        });
    });
    
    $('#users-box').on('click', '.unregister-user-from-session', function () {
        var sessionUserId = $(this).data('session-user-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_session_unregister_user',
                {'sessionUser': sessionUserId}
            ),
            removeUserRow,
            sessionUserId,
            Translator.trans('unregister_user_from_session_message', {}, 'cursus'),
            Translator.trans('unregister_user_from_session', {}, 'cursus')
        );
    });

    var removeUserRow = function (event, sessionUserId) {
        $('#row-session-user-' + sessionUserId).remove();
    };

    var refreshPage = function () {
        window.location.reload();
    }
})();