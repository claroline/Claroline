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
        var firstName = $(this).data('user-first-name');
        var lastName = $(this).data('user-last-name');
        var username = $(this).data('user-username');
        var cursusIdsTxt = '' + $('#cursus-datas-box').data('unlocked-cursus-ids');
        var cursusIds = cursusIdsTxt.split(';');
        var parameters = {};
        parameters.cursusIds = cursusIds;
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
//                var userRow = '<tr id="row-user-' + userId + '">' +
//                    '<td>' + firstName + '</td>' +
//                    '<td>' + lastName + '</td>' +
//                    '<td>' + username + '</td>' +
//                    '<td class="text-center">' +
//                    '<span class="btn btn-danger btn-sm pointer-hand unregister-btn" data-user-id="' + userId +
//                    '" data-user-first-name="' + firstName +
//                    '" data-user-last-name="' + lastName +
//                    '" data-user-username="' + username +
//                    '">' +
//                    Translator.trans('unregister', {}, 'team') +
//                    '</span>' +
//                    '</td>' +
//                    '</tr>';
//                $('#users-list-table').append(userRow);
//
//                if ($('#users-list').hasClass('hidden')) {
//                    $('#no-user-alert').addClass('hidden');
//                    $('#users-list').removeClass('hidden');
//                }
            }
        });
    });

    $('#view-registration-box').on('click', '.register-group-btn', function () {
        var groupId = $(this).data('group-id');
        var groupName = $(this).data('group-name');
        var cursusIdsTxt = '' + $('#cursus-datas-box').data('unlocked-cursus-ids');
        var cursusIds = cursusIdsTxt.split(';');
        var parameters = {};
        parameters.cursusIds = cursusIds;
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

    var removeUserRow = function (event, cursusUserId) {
        $('#row-user-' + cursusUserId).remove();
    };

    var refreshPage = function () {
        window.location.reload();
    };
})();