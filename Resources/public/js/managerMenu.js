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
        var registerBtn = $(this);
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
                registerBtn.removeClass('btn-success');
                registerBtn.removeClass('register-btn');
                registerBtn.addClass('btn-danger');
                registerBtn.addClass('unregister-btn');
                registerBtn.html(Translator.get('team:unregister'));
                
                var maxUsers = $('#registration-users-list-datas').data('max-users');
                var nbUsers = parseInt($('#nb-users-' + teamId).text());
                nbUsers++;
                $('#nb-users-' + teamId).html(nbUsers);
                
                if (maxUsers !== '' && nbUsers >= parseInt(maxUsers)) {
                    $('.register-btn').addClass('disabled');
                }
            }
        });
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
                unregisterBtn.html(Translator.get('team:register'));
                
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
        var workspaceId = $(this).data('workspace-id');
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
            {'workspace': workspaceId }
        );
        route += '?' + $.param(queryString);

        window.Claroline.Modal.confirmRequest(
            route,
            refreshPage,
            null,
            Translator.get('team:delete_selected_teams_comfirm_message'),
            Translator.get('team:delete_teams')
        );
    });
    
    $('#empty-teams-btn').on('click', function () {
        var workspaceId = $(this).data('workspace-id');
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
            {'workspace': workspaceId }
        );
        route += '?' + $.param(queryString);

        window.Claroline.Modal.confirmRequest(
            route,
            refreshPage,
            null,
            Translator.get('team:empty_selected_teams_comfirm_message'),
            Translator.get('team:empty_teams')
        );
    });
   
    $('#fill-teams-btn').on('click', function () {
        var workspaceId = $(this).data('workspace-id');
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
            {'workspace': workspaceId }
        );
        route += '?' + $.param(queryString);

        window.Claroline.Modal.confirmRequest(
            route,
            refreshPage,
            null,
            Translator.get('team:fill_selected_teams_comfirm_message'),
            Translator.get('team:fill_teams')
        );
    });
    
    var removeTeamRow = function () {
        $('#row-team-' + currentTeamId).remove();
    };
    
    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    };
})();