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
    
    var sessionsClosed = true;
    var currentSessionId = 0;
    
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
    
    $('#users-box').on('click', '#register-groups-btn', function () {
        var sessionId = $(this).data('session-id');
        var title = Translator.trans('register_groups_to_session', {}, 'cursus');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_session_registration_unregistered_groups_list',
                {
                    'session': sessionId,
                    'groupType': 0
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
            success: function (datas) {
                $('#registration-row-user-' + userId).remove();
                
                for (var i = 0; i < datas.length; i++) {
                    var sessionUserElement =
                        '<tr id="row-session-user-' + datas[i]['id'] + '">' +
                            '<td>' +
                                datas[i]['user_first_name'] + ' ' +
                                datas[i]['user_last_name'] +
                                ' <small>(' + datas[i]['username'] + ')</small>' +
                                ' &nbsp;' +
                                '<i class="fa fa-envelope-o user-send-confirmation-mail-btn pointer-hand"' +
                                   ' data-toggle="tooltip"' +
                                   ' data-placement="top"' +
                                   ' data-container="#session-management-box"' +
                                   ' title="' + Translator.trans('send_confirmation_mail_to_user', {}, 'cursus') + '"' +
                                   ' data-session-id="' + sessionId + '"' +
                                   ' data-user-id="' + datas[i]['user_id'] + '"' +
                                '>' +    
                                '</i> ' +
                                '<i class="fa fa-times-circle unregister-user-from-session pointer-hand"' +
                                   ' data-toggle="tooltip"' +
                                   ' data-placement="top"' +
                                   ' data-container="#session-management-box"' +
                                   ' title="' + Translator.trans('unregister_tutor_from_session', {}, 'cursus') + '"' +
                                   ' data-session-user-id="' + datas[i]['id'] + '"' +
                                   ' style="color: #D9534F"' +
                                '>' +
                                '</i>' +
                            '</td>' +
                        '</tr>';
                
                    if (parseInt(datas[i]['user_type']) === 0) {
                        $('#session-learners-table').append(sessionUserElement);
                    } else if (parseInt(datas[i]['user_type']) === 1) {
                        $('#session-tutors-table').append(sessionUserElement);
                    }
                }
            }
        });
    });
    
    $('#view-registration-box').on('click', '.register-group-btn', function () {
        var groupId = $(this).data('group-id');
        var sessionId = $(this).data('session-id');
        var groupType = $(this).data('group-type');
    
        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_session_register_group',
                {
                    'session': sessionId,
                    'group': groupId,
                    'groupType': groupType
                }
            ),
            type: 'POST',
            success: function () {
                $('#registration-row-group-' + groupId).remove();
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
    
    $('#users-box').on('click', '.unregister-group-from-session', function () {
        var sessionGroupId = $(this).data('session-group-id');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_session_unregister_group',
                {'sessionGroup': sessionGroupId}
            ),
            removeGroupRow,
            sessionGroupId,
            Translator.trans('unregister_group_from_session_message', {}, 'cursus'),
            Translator.trans('unregister_group_from_session', {}, 'cursus')
        );
    });
    
    $('#show-closed-sessions-btn').on('click', function () {
        sessionsClosed = !sessionsClosed;
        
        if (sessionsClosed) {
            $('.closed-session').addClass('hide');
        } else {
            $('.closed-session').removeClass('hide');
        }
    });
    
    $('.edit-session-btn').on('click', function () {
        var sessionId = $(this).data('session-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_cursus_course_session_edit_form',
                {'session': sessionId}
            ),
            refreshPage,
            function() {}
        );
    });
    
    $('.delete-session-btn').on('click', function () {
        var sessionId = $(this).data('session-id');
        var sessionName = $(this).data('session-name');
        currentSessionId = sessionId;
        $('#with-workspace-chk').prop('checked', false);
        $('#delete-session-name-header').html(sessionName);
        $('#delete-session-box').modal('show');
    });
    
    $('#confirm-session-deletion-btn').on('click', function () {
        var checked = $('#with-workspace-chk').prop('checked');
        var mode = checked ? 1 : 0;
        
        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_session_delete',
                {
                    'session': currentSessionId,
                    'mode': mode
                }
            ),
            type: 'DELETE',
            success: function () {
                $('#row-session-' + currentSessionId).remove();
                $('#delete-session-box').modal('hide');
            }
        });
    });
    
    $('#sessions-box').on('click', '.session-send-confirmation-mail-btn', function () {
        var sessionId = $(this).data('session-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_session_confirmation_mail_send',
                {'session': sessionId}
            ),
            doNothing,
            null,
            Translator.trans('send_confirmation_mail_to_session_message', {}, 'cursus'),
            Translator.trans('send_confirmation_mail', {}, 'cursus')
        );
    });
    
    $('#users-box').on('click', '.user-send-confirmation-mail-btn', function () {
        var sessionId = $(this).data('session-id');
        var userId = $(this).data('user-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_session_user_confirmation_mail_send',
                {'session': sessionId, 'user': userId}
            ),
            doNothing,
            null,
            Translator.trans('send_confirmation_mail_to_user_message', {}, 'cursus'),
            Translator.trans('send_confirmation_mail', {}, 'cursus')
        );
    });
    
    $('#users-box').on('click', '.accept-registration-btn', function () {
        var queueId = $(this).data('queue-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_session_user_registration_accept',
                {'queue': queueId}
            ),
            removeQueuedUserManagementBox,
            queueId,
            Translator.trans('accept_registration_confirm_message', {}, 'cursus'),
            Translator.trans('accept_registration', {}, 'cursus')
        );
    });
    
    $('#users-box').on('click', '.decline-registration-btn', function () {
        var queueId = $(this).data('queue-id');
        
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_cursus_course_session_user_registration_decline',
                {'queue': queueId}
            ),
            removeQueuedUser,
            queueId,
            Translator.trans('decline_registration_confirm_message', {}, 'cursus'),
            Translator.trans('decline_registration', {}, 'cursus')
        );
    });
    
    $('.transfer-to-session-btn').on('click', function () {
        var queueId = $(this).data('queue-id')
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_cursus_course_queued_user_transfer_form',
                {'queue': queueId}
            ),
            removeCourseQueueRow,
            function() {}
        );
    });
    
    $('#sessions-box').on('click', '.default-session-btn', function () {
        var sessionId = $(this).data('session-id');
        var currentBtn = $(this);
        
        $.ajax({
            url: Routing.generate(
                'claro_cursus_course_session_default_switch',
                {'session': sessionId}
            ),
            type: 'POST',
            success: function (datas) {
                var content = datas['default'] ?
                    '<i class="fa fa-check-circle" style="color: #5CB85C"></i>' :
                    '<i class="fa fa-times-circle" style="color: #D9534F"></i>';
                currentBtn.html(content);
            }
        });
    });

    var removeUserRow = function (event, sessionUserId) {
        $('#row-session-user-' + sessionUserId).remove();
    };

    var removeGroupRow = function (event, sessionGroupId) {
        $('#row-session-group-' + sessionGroupId).remove();
    };

    var refreshPage = function () {
        window.location.reload();
    };
    
    var doNothing = function () {};
    
    var removeQueuedUserManagementBox = function (event, queueId) {
        $('#row-queued-user-management-' + queueId).remove();
    };
    
    var removeQueuedUser = function(event, queueId) {
        $('#row-queued-user-' + queueId).remove();
    };
    
    var removeCourseQueueRow = function (queueId) {
        $('#queued-user-row-' + queueId).remove();
    };
})();