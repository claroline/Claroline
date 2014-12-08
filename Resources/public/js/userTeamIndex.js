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

    $('#unregister-btn').on('click', function () {
        var teamId = $(this).data('team-id');
        var teamName = $(this).data('team-name');

        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_team_self_unregister_user',
                {'team': teamId}
            ),
            teamMenuPage,
            null,
            Translator.trans('unregister_from_team_confirm_message', {}, 'team'),
            teamName
        );
    });

    $('#edit-team-btn').on('click', function () {
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

    $('.unregister-user-btn').on('click', function () {
        var teamId = $(this).data('team-id');
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

    var teamMenuPage = function () {
        var workspaceId = $('#datas-block').data('workspace-id');
        window.location = Routing.generate(
            'claro_team_user_menu',
            {'workspace': workspaceId}
        );
    };

    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    };

    var removeUserRow = function (event, userId) {
        $('#row-user-' + userId).remove();
    };
})();
