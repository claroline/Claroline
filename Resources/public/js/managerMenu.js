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
    
    $('.delete-team-btn').on('click', function () {
        currentTeamId = $(this).data('team-id');
        window.Claroline.Modal.confirmRequest(
            Routing.generate(
                'claro_team_delete',
                {'team': currentTeamId}
            ),
            removeTeamRow,
            null,
            Translator.get('team:delete_team_comfirm_message'),
            Translator.get('team:delete_team')
        );
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
        var userId = $(this).data('user-id');
        var teamId = $(this).data('team-id');
        
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
                refreshPage();
            }
        });
    });
    
    $('#view-registration-users-box').on('click', '.unregister-btn', function () {
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
                refreshPage();
            }
        });
    });
    
    var removeTeamRow = function () {
        $('#row-team-' + currentTeamId).remove();
    };
    
    var refreshPage = function () {
        window.location.reload();
    };
})();