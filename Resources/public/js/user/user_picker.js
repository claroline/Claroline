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
    
    $('body').on('click', '.user-picker', function (event) {
        var pickerName = $(this).data('picker-name');
        var pickerTitle = $(this).data('picker-title');
        var multiple = $(this).data('multiple');
        var showAllUsers = $(this).data('show-all-users');
        var showFilters = $(this).data('show-filters');
        var showUsername = $(this).data('show-username');
        var showMail = $(this).data('show-mail');
        var showCode = $(this).data('show-code');
        
        pickerName = (pickerName === undefined) ?
            'picker-name' :
            pickerName;
        pickerTitle = (pickerTitle === undefined) ?
            Translator.trans('user_selector', {}, 'platform') :
            pickerTitle; 
        multiple = (multiple === undefined) ?
            'multiple' :
            multiple;
        showAllUsers = (showAllUsers === undefined) ?
            0 :
            showAllUsers;
        showFilters = (showFilters === undefined) ?
            1 :
            showFilters;
        showUsername = (showUsername === undefined) ?
            1 :
            showUsername;
        showMail = (showMail === undefined) ?
            0 :
            showMail;
        showCode = (showCode === undefined) ?
            0 :
            showCode;
        
        var userIdsTxt = '' + $(this).data('excluded-users');
        userIdsTxt = userIdsTxt.trim();
        var userIds = (userIdsTxt === 'undefined' || userIdsTxt === '') ?
            [] :
            userIdsTxt.split(';');

        var forcedUserIdsTxt = '' + $(this).data('forced-users');
        forcedUserIdsTxt = forcedUserIdsTxt.trim();
        var forcedUserIds = (forcedUserIdsTxt === 'undefined' || forcedUserIdsTxt === '') ?
            [] :
            forcedUserIdsTxt.split(';');

        var selectedUserIdsTxt = '' + $(this).data('selected-users');
        selectedUserIdsTxt = selectedUserIdsTxt.trim();
        var selectedUserIds = (selectedUserIdsTxt === 'undefined' || selectedUserIdsTxt === '') ?
            [] :
            selectedUserIdsTxt.split(';');
    
        var groupIdsTxt = '' + $(this).data('forced-groups');
        groupIdsTxt = groupIdsTxt.trim();
        var forcedGroupIds = (groupIdsTxt === 'undefined' || groupIdsTxt === '') ?
            [] :
            groupIdsTxt.split(';');
    
        var roleIdsTxt = '' + $(this).data('forced-roles');
        roleIdsTxt = roleIdsTxt.trim();
        var forcedRoleIds = (roleIdsTxt === 'undefined' || roleIdsTxt === '') ?
            [] :
            roleIdsTxt.split(';');
    
        var workspaceIdsTxt = '' + $(this).data('forced-workspaces');
        workspaceIdsTxt = workspaceIdsTxt.trim();
        var forcedWorkspaceIds = (workspaceIdsTxt === 'undefined' || workspaceIdsTxt === '') ?
            [] :
            workspaceIdsTxt.split(';');
    
        var parameters = {};
        parameters.excludedUserIds = userIds;
        parameters.forcedUserIds = forcedUserIds;
        parameters.selectedUserIds = selectedUserIds;
        parameters.forcedGroupIds = forcedGroupIds;
        parameters.forcedRoleIds = forcedRoleIds;
        parameters.forcedWorkspaceIds = forcedWorkspaceIds;

        var route = Routing.generate(
            'claro_user_picker',
            {
                'pickerName': pickerName,
                'pickerTitle': pickerTitle,
                'mode': multiple,
                'showAllUsers': showAllUsers,
                'showFilters': showFilters,
                'showUsername': showUsername,
                'showMail': showMail,
                'showCode': showCode
            }
        );
        route += '?' + $.param(parameters);
        
        window.Claroline.Modal.fromUrl(
            route,
            null
        );
    });
})();