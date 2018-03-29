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

    var currentTeamId;
    var workspaceId = $('#datas-box').data('workspace-id');

    $('.delete-team-btn').on('click', function () {
        currentTeamId = $(this).data('team-id');
        var teamName = $(this).data('team-name');
        $('#delete-team-directory-chk').prop('checked', false);
        $('#delete-team-validation-header').html(teamName);
        $('#delete-team-validation-box').modal('show');
    });

    $('#delete-team-confirm-btn').on('click', function () {
        var deleteDirectory = $('#delete-team-directory-chk').prop('checked') ?
            1 :
            0;

        $.ajax({
            url: Routing.generate(
                'claro_team_delete',
                {
                    'team': currentTeamId,
                    'withDirectory': deleteDirectory
                }
            ),
            type: 'POST',
            success: function () {
                $('#row-team-' + currentTeamId).remove();
                $('#delete-team-validation-box').modal('hide');
            }
        });
    });

    $('.register-users-btn').on('click', function () {
        currentTeamId = $(this).data('team-id');
        var teamName = $(this).data('team-name');

        $.ajax({
            url: Routing.generate(
                'claro_team_registration_users_list',
                {'team': currentTeamId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-registration-users-header').html(teamName);
                $('#view-registration-users-body').html(datas);
                $('#view-registration-users-box').modal('show');
            }
        });
    });

    $('#view-registration-users-box').on('click', '.register-btn', function () {
        var registerBtn = $(this);
        var userId = $(this).data('user-id');
        var teamId = $(this).data('team-id');

        //to avoid registering through a disabled button
        if (!$(this).hasClass('disabled')) {
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
                    registerBtn.removeClass('btn-success');
                    registerBtn.removeClass('register-btn');
                    registerBtn.addClass('btn-danger');
                    registerBtn.addClass('unregister-btn');
                    registerBtn.html(Translator.trans('unregister', {}, 'team'));

                    var maxUsers = $('#registration-users-list-datas').data('max-users');
                    var nbUsers = parseInt($('#nb-users-' + teamId).text());
                    nbUsers++;
                    $('#nb-users-' + teamId).html(nbUsers);

                    if (maxUsers !== '' && nbUsers >= parseInt(maxUsers)) {
                        $('.register-btn').addClass('disabled');
                    }
                }
            });
        }
    });

    $('#view-registration-users-box').on('click', '.unregister-btn', function () {
        var unregisterBtn = $(this);
        var userId = $(this).data('user-id');
        var teamId = $(this).data('team-id');

        $.ajax({
            url: Routing.generate(
                'claro_team_manager_unregister_user',
                {
                    'team': teamId,
                    'user': userId
                }
            ),
            type: 'POST',
            success: function () {
                unregisterBtn.removeClass('btn-danger');
                unregisterBtn.removeClass('unregister-btn');
                unregisterBtn.addClass('btn-success');
                unregisterBtn.addClass('register-btn');
                unregisterBtn.html(Translator.trans('register', {}, 'team'));

                var nbUsers = parseInt($('#nb-users-' + teamId).text());
                nbUsers--;
                $('#nb-users-' + teamId).html(nbUsers);
                $('.register-btn').removeClass('disabled');
            }
        });
    });

    $('.view-users-list-btn').on('click', function () {
        var teamId = $(this).data('team-id');
        var teamName = $(this).data('team-name');

        $.ajax({
            url: Routing.generate(
                'claro_team_users_list',
                {'team': teamId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-registered-users-header').html(teamName);
                $('#view-registered-users-body').html(datas);
                $('#view-registered-users-box').modal('show');
            }
        });
    });

    $('.view-team-description-btn').on('click', function () {
        var teamId = $(this).data('team-id');
        var teamName = $(this).data('team-name');

        $.ajax({
            url: Routing.generate(
                'claro_team_display_description',
                {'team': teamId}
            ),
            type: 'GET',
            success: function (datas) {
                $('#view-team-description-header').html(teamName);
                $('#view-team-description-body').html(datas);
                $('#view-team-description-box').modal('show');
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
        var teamId = $(this).data('team-id');
        var search = $('#search-user-input').val();

        $.ajax({
            url: Routing.generate(
                'claro_team_registration_users_list',
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

    $('.edit-team-btn').on('click', function () {
        var teamId = $(this).data('team-id');

        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_team_edit_form',
                {'team': teamId}
            ),
            refreshPage,
            function() {}
        );
    });

    $('.team-chk').on('change', function () {
        var nbChecked = $('.team-chk:checked').length;

        if (nbChecked > 0) {
            $('.teams-action-btn').removeClass('disabled');
        } else {
            $('.teams-action-btn').addClass('disabled');
        }
    });

    $('#delete-teams-btn').on('click', function () {
        $('#delete-teams-directory-chk').prop('checked', false);
        $('#delete-teams-validation-box').modal('show');
    });

    $('#delete-teams-confirm-btn').on('click', function () {
        var deleteDirectory = $('#delete-teams-directory-chk').prop('checked') ?
            1 :
            0;
        var i = 0;
        var queryString = {};
        var teams = [];
        $('.team-chk:checked').each(function (index, element) {
            teams[i] = element.value;
            i++;
        });
        queryString.teams = teams;

        var route = Routing.generate(
            'claro_team_manager_delete_teams',
            {
                'workspace': workspaceId,
                'withDirectory': deleteDirectory
            }
        );
        route += '?' + $.param(queryString);

        $.ajax({
            url: route,
            type: 'POST',
            success: function () {
                window.location.reload();
            }
        });
    });

    $('#empty-teams-btn').on('click', function () {
        var i = 0;
        var queryString = {};
        var teams = [];
        $('.team-chk:checked').each(function (index, element) {
            teams[i] = element.value;
            i++;
        });
        queryString.teams = teams;

        var route = Routing.generate(
            'claro_team_manager_empty_teams',
            {'workspace': workspaceId}
        );
        route += '?' + $.param(queryString);

        window.Claroline.Modal.confirmRequest(
            route,
            refreshPage,
            null,
            Translator.trans('empty_selected_teams_confirm_message', {}, 'team'),
            Translator.trans('empty_teams', {}, 'team')
        );
    });

    $('#fill-teams-btn').on('click', function () {
        var i = 0;
        var queryString = {};
        var teams = [];
        $('.team-chk:checked').each(function (index, element) {
            teams[i] = element.value;
            i++;
        });
        queryString.teams = teams;

        var route = Routing.generate(
            'claro_team_manager_fill_teams',
            {'workspace': workspaceId}
        );
        route += '?' + $.param(queryString);

        window.Claroline.Modal.confirmRequest(
            route,
            refreshPage,
            null,
            Translator.trans('fill_selected_teams_confirm_message', {}, 'team'),
            Translator.trans('fill_teams', {}, 'team')
        );
    });

    $('.team-directory-btn').on('click', function () {
        var directoryNodeId = $(this).data('node-id');

        window.location = Routing.generate(
            'claro_workspace_open_tool',
            {
                'workspaceId': workspaceId,
                'toolName': 'resource_manager'
            }
        ) + '#resources/' + directoryNodeId;
    });

    var refreshPage = function () {
        window.location.reload();
    };
})();
