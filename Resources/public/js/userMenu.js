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

    $('.register-btn').on('click', function () {
        var teamId = $(this).data('team-id');
        var teamName = $(this).data('team-name');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_team_self_register_user',
                {'team': teamId}
            ),
            refreshPage,
            null,
            Translator.trans('register_to_team_confirm_message', {}, 'team'),
            teamName
        );
    });

    $('.unregister-btn').on('click', function () {
        var teamId = $(this).data('team-id');
        var teamName = $(this).data('team-name');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_team_self_unregister_user',
                {'team': teamId}
            ),
            refreshPage,
            null,
            Translator.trans('unregister_from_team_confirm_message', {}, 'team'),
            teamName
        );
    });

    $('.team-directory-btn').on('click', function () {
        var directoryNodeId = $(this).data('node-id');
        var workspaceId = $(this).data('workspace-id');

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
