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
//                refreshPage();
                var nbUsers = parseInt($('#nb-users-' + teamId).text());
                nbUsers++;
                $('#nb-users-' + teamId).html(nbUsers);
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

    $('#view-registration-users-body').on('click', '#search-user-btn', function (event) {
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
    
    var removeTeamRow = function () {
        $('#row-team-' + currentTeamId).remove();
    };
    
    var refreshPage = function () {
        window.location.reload();
    };
})();