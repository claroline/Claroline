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

    $('#search-workspace-button').click(function () {
        var search = document.getElementById('search-workspace-txt').value;

        if (search !== '') {
            window.location.href = Routing.generate('claro_admin_registration_management_search', {
                'search': search
            });
        } else {
            window.location.href = Routing.generate('claro_admin_registration_management');
        }
    });

    $('.accordion-checkbox').click(function (event) {
        event.stopPropagation();
        var categoryElement = event.currentTarget;
        var checkedValue = $(categoryElement).is(':checked');
        var value = $(categoryElement).attr('value');
        var subMenus = 'input[class^="chk-workspaces-' + value + '"]';
        var subElements = 'input[class^="chk-workspace-' + value + '"]';
        
        $(subMenus).each(function (index, element) {
            $(element).prop('checked', checkedValue);
        });
        $(subElements).each(function (index, element) {
            $(element).prop('checked', checkedValue);
        });
        
        if (checkedValue) {
            var parentMenus = $(categoryElement).parents('.panel-root');
            $(parentMenus).each(function (index, element) {
                var currentMenuId = $(element).attr('current-index');
                var menuClass = '.chk-workspaces-' + currentMenuId;
                $(menuClass + '.accordion-checkbox.workspace-linked').prop('checked', checkedValue);
            });
        }

        if ($('.workspace-check:checked').length > 0 || $('.workspace-linked:checked').length > 0) {
            $('.subscribe-user-button, .subscribe-group-button').attr('disabled', false);
        }
        else {
            $('.subscribe-user-button, .subscribe-group-button').attr('disabled', 'disabled');
        }
    });

    $('.subscribe-user-button, .subscribe-group-button').click(function () {
        var route;
        var type = $(this).attr('subject-type');
        var parameters = {};
        var array = [];
        var i = 0;
        $('.workspace-check:checked').each(function (index, element) {
            
            if (array.indexOf(element.value) === -1) {
                array[i] = element.value;
                i++;
            }
        });
        $('.workspace-linked:checked').each(function (index, element) {
            var workspaceId = parseInt($(element).attr('workspace-id'), 10);
            
            if (array.indexOf(workspaceId) === -1) {
                array[i] = workspaceId;
                i++;
            }
        });
        parameters.ids = array;

        if (type === 'user') {
            route = Routing.generate('claro_admin_registration_management_users');
        }
        else {
            route = Routing.generate('claro_admin_registration_management_groups');
        }
        route += '?' + $.param(parameters);

        window.location.href = route;
    });

    $('#workspace-list-div').on('click', '.workspace-check', function () {
        if ($('.workspace-check:checked').length > 0 || $('.workspace-linked:checked').length > 0) {
            $('.subscribe-user-button, .subscribe-group-button').attr('disabled', false);
        }
        else {
            $('.subscribe-user-button, .subscribe-group-button').attr('disabled', 'disabled');
        }
    });
    
    $('.workspace-field').on('click', function () {
        var workspaceId = $(this).data('workspace-id');
        var route = Routing.generate(
            'claro_admin_workspace_users_unregistration_management', 
            {'workspace': workspaceId}
        );

        window.location.href = route;
    });
    
    $('.workspace-field').addClass('pointer-hand');
})();
