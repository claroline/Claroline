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
    
    $('#edit-params-btn').on('click', function () {
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
                    '<span class="btn btn-danger btn-sm pointer-hand unregister-btn" data-user-id="' + userId + '">' +
                    Translator.get('team:unregister') +
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
                nbUsers--;
                $('#row-user-' + userId).remove();
            }
        });
    });
    
    var refreshPage = function () {
        window.tinymce.claroline.disableBeforeUnload = true;
        window.location.reload();
    };
})();