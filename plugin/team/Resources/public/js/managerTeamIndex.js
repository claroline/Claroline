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

    var teamId = $('#team-datas').data('team-id');
    var maxUsers = $('#team-datas').data('max-users');
    var nbUsers = $('#team-datas').data('nb-users');
    var workspaceId = $('#team-datas').data('workspace-id');
    var currentManagerId = $('#team-datas').data('team-manager-id');

    $('#edit-params-btn').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_team_edit_form',
                {'team': teamId}
            ),
            refreshPage,
            function() {}
        );
    });

    $('#register-users-btn').on('click', function () {

        $.ajax({
            url: Routing.generate(
                'claro_team_registration_unregistered_users_list',
                {'team': teamId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-registration-users-body').html(datas);
                $('#view-registration-users-box').modal('show');
            }
        });
    });

    $('#view-registration-users-body').on('click', 'a', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var url = $(this).attr('href');

        $.ajax({
            url: url,
            type: 'GET',
            async: false,
            success: function (result) {
                $('#view-registration-users-body').html(result);
            }
        });
    });

    $('#view-registration-users-body').on('click', '#search-user-btn', function () {
        var search = $('#search-user-input').val();

        $.ajax({
            url: Routing.generate(
                'claro_team_registration_unregistered_users_list',
                {
                    'team': teamId,
                    'search': search
                }
            ),
            type: 'GET',
            async: false,
            success: function (result) {
                $('#view-registration-users-body').html(result);
            }
        });
    });

    $('#view-registration-users-box').on('click', '.register-btn', function () {
        var userId = $(this).data('user-id');
        var firstName = $(this).data('user-first-name');
        var lastName = $(this).data('user-last-name');
        var username = $(this).data('user-username');

        $.ajax({
            url: Routing.generate(
                'claro_team_manager_register_user',
                {
                    'team': teamId,
                    'user': userId
                }
            ),
            type: 'POST',
            success: function () {
                nbUsers++;
                $('#registration-row-user-' + userId).remove();
                var userRow = '<tr id="row-user-' + userId + '">' +
                    '<td>' + firstName + '</td>' +
                    '<td>' + lastName + '</td>' +
                    '<td>' + username + '</td>' +
                    '<td class="text-center">' +
                    '<span class="btn btn-danger btn-sm pointer-hand unregister-btn" data-user-id="' + userId +
                    '" data-user-first-name="' + firstName +
                    '" data-user-last-name="' + lastName +
                    '" data-user-username="' + username +
                    '">' +
                    Translator.trans('unregister', {}, 'team') +
                    '</span>' +
                    '</td>' +
                    '</tr>';
                $('#users-list-table').append(userRow);

                if ($('#users-list').hasClass('hidden')) {
                    $('#no-user-alert').addClass('hidden');
                    $('#users-list').removeClass('hidden');
                }

                if (maxUsers !== '' && nbUsers >= parseInt(maxUsers)) {
                    $('.register-btn').addClass('disabled');
                }
            }
        });
    });

    $('#users-list-table').on('click', '.unregister-btn', function () {
        var userId = $(this).data('user-id');
        var firstName = $(this).data('user-first-name');
        var lastName = $(this).data('user-last-name');
        var username = $(this).data('user-username');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_team_manager_unregister_user',
                {
                    'team': teamId,
                    'user': userId
                }
            ),
            removeUserRow,
            userId,
            Translator.trans('unregister_user_from_team_confirm_message', {}, 'team'),
            firstName + ' ' + lastName + ' (' + username + ')'
        );
    });

    $('#select-team-manager-btn').on('click', function () {
        var userPicker = new UserPicker();
        var params = {
            picker_name: 'team-manager-picker',
            picker_title: Translator.trans('team_manager_selection', {}, 'team'),
            forced_workspaces: [workspaceId]
        };
        
        if (currentManagerId !== -1) {
            params['selected_users'] = [currentManagerId];
        }
        userPicker.configure(params, registerManager);
        userPicker.open();
    });

    $('body').on('click', '#remove-team-manager-btn', function () {
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_team_manager_unregister_manager',
                {'team': teamId}
            ),
            emptyTeamManagerBox,
            null,
            Translator.trans('remove_team_manager_confirm_message', {}, 'team'),
            Translator.trans('remove_team_manager', {}, 'team')
        );
        currentManagerId = -1;
    });
    
    var registerManager = function (userId) {
        
        if (userId !== null) {
            currentManagerId = userId;
            
            $.ajax({
                url: Routing.generate(
                    'claro_user_infos_request',
                    {'user': userId}
                ),
                type: 'GET',
                success: function (datas) {
                    var firstName = datas['firstName'];
                    var lastName = datas['lastName'];

                    $.ajax({
                        url: Routing.generate(
                            'claro_team_manager_register_manager',
                            {'team': teamId, 'user': userId}
                        ),
                        type: 'POST',
                        async: false,
                        success: function () {
                            var managerBox = firstName + ' ' + lastName + ' ' +
                                '<span>' +
                                    '<i id="remove-team-manager-btn"' +
                                       'class="fa fa-times-circle pointer-hand"' +
                                       'style="color: #D9534F"' +
                                       'data-toggle="tooltip"' +
                                       'data-placement="top"' +
                                       'title="' + Translator.trans('remove_team_manager', {}, 'team') + '"' +
                                    '>' +
                                    '</i>' +
                                '</span>';
                            $('#team-manager-box').html(managerBox);
                        }
                    });
                }
            });
        }
    };

    var refreshPage = function () {
        window.location.reload();
    };

    var removeUserRow = function (event, userId) {
        nbUsers--;
        $('#row-user-' + userId).remove();
    };

    var emptyTeamManagerBox = function () {
        var noneMsg = '[' + Translator.trans('none', {}, 'platform') + ']';
        $('#team-manager-box').html(noneMsg);
    };
})();
