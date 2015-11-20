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
    
    var cursusId = $('#cursus-datas-box').data('cursus-id');
    
    function activateGroupsUnregistrationBtn()
    {
        var nbChecked = $('.chk-group-item:checked').length;
        
        if (nbChecked > 0) {
            $('#unregister-selected-groups-btn').removeClass('disabled');
        } else {
            $('#unregister-selected-groups-btn').addClass('disabled');
        }
    }
    
    function activateUsersUnregistrationBtn()
    {
        var nbChecked = $('.chk-user-item:checked').length;
        
        if (nbChecked > 0) {
            $('#unregister-selected-users-btn').removeClass('disabled');
        } else {
            $('#unregister-selected-users-btn').addClass('disabled');
        }
    }
    
    $('#register-groups-btn').on('click', function () {
        var title = Translator.trans('register_groups_to_cursus', {}, 'cursus');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_registration_unregistered_groups_list',
                {'cursus': cursusId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-registration-header').html(title);
                $('#view-registration-body').html(datas);
                $('#view-registration-box').modal('show');
            }
        });
    });
    
    $('#register-users-btn').on('click', function () {
        var title = Translator.trans('register_users_to_cursus', {}, 'cursus');

        $.ajax({
            url: Routing.generate(
                'claro_cursus_registration_unregistered_users_list',
                {'cursus': cursusId}
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

        $.ajax({
            url: Routing.generate(
                'claro_cursus_registration_unregistered_users_list',
                {
                    'cursus': cursusId,
                    'search': search
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
            var orderedBy = $(this).data('ordered-by');
            var order = $(this).data('order');
            var max = $(this).data('max');
            var search = $(this).val();

            $.ajax({
                url: Routing.generate(
                    'claro_cursus_registration_unregistered_users_list',
                    {
                        'cursus': cursusId,
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
    
    $('#view-registration-body').on('click', '#search-group-btn', function () {
        var search = $('#search-group-input').val();

        $.ajax({
            url: Routing.generate(
                'claro_cursus_registration_unregistered_groups_list',
                {
                    'cursus': cursusId,
                    'search': search
                }
            ),
            type: 'GET',
            success: function (result) {
                $('#view-registration-body').html(result);
            }
        });
    });

    $('#view-registration-body').on('keypress', '#search-group-input', function (e) {
        if (e.keyCode === 13) {
            var orderedBy = $(this).data('ordered-by');
            var order = $(this).data('order');
            var max = $(this).data('max');
            var search = $(this).val();

            $.ajax({
                url: Routing.generate(
                    'claro_cursus_registration_unregistered_groups_list',
                    {
                        'cursus': cursusId,
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
        var cursusIdsTxt = '' + $('#cursus-datas-box').data('unlocked-cursus-ids');
        var cursusIds = cursusIdsTxt.split(';');
        var parameters = {};
        parameters.cursusIds = cursusIds;
        var route = Routing.generate(
            'claro_cursus_multiple_register_user_confirm_sessions',
            {'user': userId}
        );
        route += '?' + $.param(parameters);
    
        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#course-registration-session-content').html(datas);
            }
        });
        $('#view-registration-box').modal('hide');
        $('#course-registration-session-unchecked-warning').addClass('hidden');
        $('#course-registration-session-box').modal('show');
    });
    
    $('.close-course-registration-session-box-btn').on('click', function () {
        $('#course-registration-session-box').modal('hide');
        $('#view-registration-box').modal('show');
    });
    
    $('#confirm-sessions-selection-btn').on('click', function () {
        var sessions = [];
        var allChecked = true;
        
        $('.sessions-choices-group').each(function () {
            var name = $(this).data('choices-name');
            var value = $('input[name="' + name + '"]:checked').val();
            sessions.push(value);
            
            if (value === undefined) {
                allChecked = false;
                $('#course-registration-session-unchecked-warning').removeClass('hidden');
            }
        });
        
        if (allChecked) {
            var sessionIds = [];
            
            for (var i = 0; i < sessions.length; i++) {
                
                if (sessions[i] > 0) {
                    sessionIds.push(sessions[i]);
                }
            }
            var type = $('#multiple-datas-box').data('type');
            var cursusIdsTxt = '' + $('#cursus-datas-box').data('unlocked-cursus-ids');
            var cursusIds = cursusIdsTxt.split(';');
            var parameters = {};
            parameters.cursusIds = cursusIds;
            parameters.sessionIds = sessionIds;
            
            if (type === 'user') {
                var userId = $('#multiple-datas-box').data('user-id');
                var route = Routing.generate(
                    'claro_cursus_multiple_register_user',
                    {'user': userId}
                );
                route += '?' + $.param(parameters);

                $.ajax({
                    url: route,
                    type: 'POST',
                    success: function () {
                        $('#registration-row-user-' + userId).remove();
                        $('#course-registration-session-box').modal('hide');
                        $('#view-registration-box').modal('show');
                    }
                });
            } else if (type === 'group') {
                var groupId = $('#multiple-datas-box').data('group-id');
                var route = Routing.generate(
                    'claro_cursus_multiple_register_group',
                    {'group': groupId}
                );
                route += '?' + $.param(parameters);

                $.ajax({
                    url: route,
                    type: 'POST',
                    success: function () {
                        window.location.reload();
                    }
                });
            }
        }
    });
    
    $('#close-course-registration-session-unchecked-warning').on('click', function () {
        $('#course-registration-session-unchecked-warning').addClass('hidden');
    });

    $('#view-registration-box').on('click', '.register-group-btn', function () {
        var groupId = $(this).data('group-id');
        var cursusIdsTxt = '' + $('#cursus-datas-box').data('unlocked-cursus-ids');
        var cursusIds = cursusIdsTxt.split(';');
        var parameters = {};
        parameters.cursusIds = cursusIds;
        var route = Routing.generate(
            'claro_cursus_multiple_register_group_confirm_sessions',
            {'group': groupId}
        );
        route += '?' + $.param(parameters);
    
        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#course-registration-session-content').html(datas);
            }
        });
        $('#view-registration-box').modal('hide');
        $('#course-registration-session-unchecked-warning').addClass('hidden');
        $('#course-registration-session-box').modal('show');
    });

    $('#users-list').on('click', '.unregister-user-btn', function () {
        var cursusUserId = $(this).data('cursus-user-id');
        var firstName = $(this).data('user-first-name');
        var lastName = $(this).data('user-last-name');
        var username = $(this).data('user-username');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_cursus_user_delete', {'cursusUser': cursusUserId}),
            removeUserRow,
            cursusUserId,
            Translator.trans('unregister_user_from_cursus_confirm_message', {}, 'cursus'),
            firstName + ' ' + lastName + ' (' + username + ')'
        );
    });

    $('#groups-list').on('click', '.unregister-group-btn', function () {
        var cursusGroupId = $(this).data('cursus-group-id');
        var groupName = $(this).data('group-name');

        window.Claroline.Modal.confirmRequest(
            Routing.generate('claro_cursus_group_delete', {'cursusGroup': cursusGroupId}),
            refreshPage,
            cursusGroupId,
            Translator.trans('unregister_group_from_cursus_confirm_message', {}, 'cursus'),
            groupName
        );
    });
    
    $('#check-all-groups').on('click', function () {
        var checked = $(this).prop('checked');
        $('.chk-group-item').prop('checked', checked);
        activateGroupsUnregistrationBtn();
    });
    
    $('.chk-group-item').on('change', function () {
        activateGroupsUnregistrationBtn();
    });
    
    $('#unregister-selected-groups-btn').on('click', function () {
        var ids = [];
        $('.chk-group-item:checked').each(function () {
            ids.push(parseInt($(this).val()));
        });
        var params = {};
        params.cursusGroupIds = ids;
        var route = Routing.generate('claro_cursus_groups_delete');
        route += '?' + $.param(params);
        window.Claroline.Modal.confirmRequest(
            route,
            refreshPage,
            null,
            Translator.trans('unregister_selected_groups_message', {}, 'cursus'),
            Translator.trans('unregister_selected_groups', {}, 'cursus')
        );
    });
    
    $('#check-all-users').on('click', function () {
        var checked = $(this).prop('checked');
        $('.chk-user-item').prop('checked', checked);
        activateUsersUnregistrationBtn();
    });
    
    $('.chk-user-item').on('change', function () {
        activateUsersUnregistrationBtn();
    });
    
    $('#unregister-selected-users-btn').on('click', function () {
        var ids = [];
        $('.chk-user-item:checked').each(function () {
            ids.push(parseInt($(this).val()));
        });
        var params = {};
        params.userIds = ids;
        var route = Routing.generate('claro_cursus_users_delete', {cursus: cursusId});
        route += '?' + $.param(params);
        window.Claroline.Modal.confirmRequest(
            route,
            refreshPage,
            null,
            Translator.trans('unregister_selected_users_message', {}, 'cursus'),
            Translator.trans('unregister_selected_users', {}, 'cursus')
        );
    });

    var removeUserRow = function (event, cursusUserId) {
        $('#row-user-' + cursusUserId).remove();
    };

    var refreshPage = function () {
        window.location.reload();
    };
})();