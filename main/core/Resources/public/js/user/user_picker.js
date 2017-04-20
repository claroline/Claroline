/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var UserPicker = function () {
    this.pickerName = 'picker-name';
    this.pickerTitle = Translator.trans('user_selector', {}, 'platform');
    this.multiple = 'single';
    this.showAllUsers = 0;
    this.showFilters = 1;
    this.showId = 0;
    this.showPicture = 0;
    this.showUsername = 1;
    this.showMail = 0;
    this.showCode = 0;
    this.showGroups = 0;
    this.showPlatformRoles = 0;
    this.attachName = 1;
    this.filterAdminOrgas = 0;
    this.returnDatas = 0;
    this.userIds = [];
    this.forcedUserIds = [];
    this.selectedUserIds = [];
    this.forcedGroupIds = [];
    this.forcedRoleIds = [];
    this.forcedWorkspaceIds = [];
    this.shownWorkspaceIds = [];
    this.parameters = {};
    this.parameters.excludedUserIds = this.userIds;
    this.parameters.forcedUserIds = this.forcedUserIds;
    this.parameters.selectedUserIds = this.selectedUserIds;
    this.parameters.forcedGroupIds = this.forcedGroupIds;
    this.parameters.forcedRoleIds = this.forcedRoleIds;
    this.parameters.forcedWorkspaceIds = this.forcedWorkspaceIds;
    this.parameters.shownWorkspaceRoleIds = this.shownWorkspaceIds;
    this.callBack = function (results) {};
};

UserPicker.prototype.configure = function (configurationDatas, callBack) {
    if (configurationDatas['picker_name'] !== undefined) {
        this.pickerName = configurationDatas['picker_name'];
    }
    if (configurationDatas['picker_title'] !== undefined) {
        this.pickerTitle = configurationDatas['picker_title'];
    }
    if (configurationDatas['multiple'] !== undefined) {
        this.multiple = configurationDatas['multiple'] ? 'multiple' : 'single';
    }
    if (configurationDatas['show_all_users'] !== undefined) {
        this.showAllUsers = configurationDatas['show_all_users'] ? 1 : 0;
    }
    if (configurationDatas['show_filters'] !== undefined) {
        this.showFilters = configurationDatas['show_filters'] ? 1 : 0;
    }
    if (configurationDatas['show_id'] !== undefined) {
        this.showId = configurationDatas['show_id'] ? 1 : 0;
    }
    if (configurationDatas['show_picture'] !== undefined) {
        this.showPicture = configurationDatas['show_picture'] ? 1 : 0;
    }
    if (configurationDatas['show_username'] !== undefined) {
        this.showUsername = configurationDatas['show_username'] ? 1 : 0;
    }
    if (configurationDatas['show_mail'] !== undefined) {
        this.showMail = configurationDatas['show_mail'] ? 1 : 0;
    }
    if (configurationDatas['show_code'] !== undefined) {
        this.showCode = configurationDatas['show_code'] ? 1 : 0;
    }

    if (configurationDatas['show_groups'] !== undefined) {
        this.showGroups = configurationDatas['show_groups'] ? 1 : 0;
    }
    if (configurationDatas['show_platform_roles'] !== undefined) {
        this.showPlatformRoles = configurationDatas['show_platform_roles'] ? 1 : 0;
    }
    if (configurationDatas['attach_name'] !== undefined) {
        this.attachName = configurationDatas['attach_name'] ? 1 : 0;
    }
    if (configurationDatas['filter_admin_orgas'] !== undefined) {
        this.filterAdminOrgas = configurationDatas['filter_admin_orgas'] ? 1 : 0;
    }
    if (configurationDatas['return_datas'] !== undefined) {
        this.returnDatas = configurationDatas['return_datas'] ? 1 : 0;
    }
    if (configurationDatas['blacklist'] !== undefined) {
        this.userIds = configurationDatas['blacklist'];
    }
    if (configurationDatas['whitelist'] !== undefined) {
        this.forcedUserIds = configurationDatas['whitelist'];
    }
    if (configurationDatas['selected_users'] !== undefined) {
        this.selectedUserIds = configurationDatas['selected_users'];
    }
    if (configurationDatas['forced_groups'] !== undefined) {
        this.forcedGroupIds = configurationDatas['forced_groups'];
    }
    if (configurationDatas['forced_roles'] !== undefined) {
        this.forcedRoleIds = configurationDatas['forced_roles'];
    }
    if (configurationDatas['forced_workspaces'] !== undefined) {
        this.forcedWorkspaceIds = configurationDatas['forced_workspaces'];
    }
    if (configurationDatas['shown_workspaces'] !== undefined) {
        this.shownWorkspaceIds = configurationDatas['shown_workspaces'];
    }
    this.parameters = {};
    this.parameters.excludedUserIds = this.userIds;
    this.parameters.forcedUserIds = this.forcedUserIds;
    this.parameters.selectedUserIds = this.selectedUserIds;
    this.parameters.forcedGroupIds = this.forcedGroupIds;
    this.parameters.forcedRoleIds = this.forcedRoleIds;
    this.parameters.forcedWorkspaceIds = this.forcedWorkspaceIds;
    this.parameters.shownWorkspaceIds = this.shownWorkspaceIds;
    this.callBack = callBack || this.callBack;
};

UserPicker.prototype.open = function () {
    var userPicker = this;
    var modal = window.Claroline.Modal;
    var route = Routing.generate(
        'claro_user_picker',
        {
            'pickerName': userPicker.pickerName,
            'pickerTitle': userPicker.pickerTitle,
            'mode': userPicker.multiple,
            'showAllUsers': userPicker.showAllUsers,
            'showFilters': userPicker.showFilters,
            'showId': userPicker.showId,
            'showPicture': userPicker.showPicture,
            'showUsername': userPicker.showUsername,
            'showMail': userPicker.showMail,
            'showCode': userPicker.showCode,
            'showGroups': userPicker.showGroups,
            'showPlatformRoles': userPicker.showPlatformRoles,
            'attachName': userPicker.attachName,
            'filterAdminOrgas': userPicker.filterAdminOrgas
        }
    );
    route += '?' + $.param(this.parameters);

    $.ajax({
        url: route,
        type: 'GET',
        success: function (modalContent) {
            var modalElement = modal.create(modalContent, 'userpicker', {'z-index': 2000});
            var modalId = '#user-picker-modal-' + userPicker.pickerName;
            var currentSearch = $(modalId + ' #user-picker-datas-box').data('search');
            var currentMax = $(modalId + ' #user-picker-datas-box').data('max');
            var currentOrderedBy = $(modalId + ' #user-picker-datas-box').data('ordered-by');
            var currentOrder = $(modalId + ' #user-picker-datas-box').data('order');
            var selectedUserNamesTxt = $(modalId + ' #user-picker-main-datas-box').data('selected-users-names');
            selectedUserNamesTxt = selectedUserNamesTxt.trim();
            var selectedUserNames = (selectedUserNamesTxt === 'undefined' || selectedUserNamesTxt === '') ?
                [] :
                selectedUserNamesTxt.split(';;;');
            var filterType = 'none';
            var secondFilterValue = 'none';
            var secondFilterName = 'none';
            var thirdFilterValue = 'none';
            var thirdFilterName = 'none';
            var groupFilters = [];
            var roleFilters = [];
            var workspaceFilters = [];
            var groupIds = [];
            var roleIds = [];
            var workspaceIds = [];
            var userIds = [];
            var selectedUsers = [];

            function displaySecondFilter()
            {
                if (filterType === 'none') {
                    resetFilters(true, true, true);
                } else {
                    $(modalId + ' #box-filter-level-2').show('slow', function () {
                        secondFilterValue = 'none';
                        thirdFilterValue = 'none';
                        $(modalId + ' #filter-level-2').val('none');
                        $(modalId + ' #filter-level-3').val('none');

                        $.ajax({
                            url: Routing.generate(
                                'claro_filters_list_for_user_picker',
                                {'filterType': filterType}
                            ),
                            type: 'GET',
                            success: function (datas) {
                                $(modalId + ' #filter-level-2').empty();
                                var option = '<option value="none" id="' + filterType + '-none">--- ' +
                                    Translator.trans('select_a_' + filterType, {}, 'platform') +
                                    ' ---</option>';
                                $(modalId + ' #filter-level-2').append(option);

                                for (var i = 0; i < datas.length; i++) {
                                    option = '<option value="' +
                                        datas[i]['id'] +
                                        '" id="' + filterType + '-' +
                                        datas[i]['id'] +
                                        '">' +
                                        datas[i]['name'] +
                                        '</option>';
                                    $(modalId + ' #filter-level-2').append(option);
                                }
                                $(modalId + ' #box-filter-level-2').show('slow', function () {
                                    $(this).removeClass('hidden');
                                });
                            }
                        });
                    });
                }
            }

            function displayThirdFilter()
            {
                if (filterType === 'workspace' && secondFilterValue !== 'none') {
                    thirdFilterValue = 'none';

                    $.ajax({
                        url: Routing.generate(
                            'claro_workspace_roles_list_for_user_picker',
                            {'workspace': secondFilterValue}
                        ),
                        type: 'GET',
                        success: function (datas) {
                            $(modalId + ' #filter-level-3').empty();
                            var option = '<option value="none" id="ws-role-none">--- ' +
                                Translator.trans('all_roles', {}, 'platform') +
                                ' ---</option>';
                            $(modalId + ' #filter-level-3').append(option);

                            for (var i = 0; i < datas.length; i++) {
                                option = '<option value="' +
                                    datas[i]['id'] +
                                    '" id="ws-role-' +
                                    datas[i]['id'] +
                                    '">' +
                                    datas[i]['name'] +
                                    '</option>';
                                $('#filter-level-3').append(option);
                            }
                            $(modalId + ' #box-filter-level-3').show('slow', function () {
                                $(this).removeClass('hidden');
                            });
                        }
                    });
                } else {
                    resetFilters(false, false, true);
                }
            }

            function displayFilterCreateButton()
            {
                if (filterType !== 'none' && secondFilterValue !== 'none') {
                    $(modalId + ' #box-filter-create-btn').show('slow', function () {
                        $(this).removeClass('hidden');
                    });
                } else {
                    $(modalId + ' #box-filter-create-btn').hide('slow');
                }
            }

            function resetFilters(first, second, third)
            {
                if (first) {
                    $(modalId + ' #filter-level-1').val('none');
                    filterType = 'none';
                }

                if (second) {
                    $(modalId + ' #box-filter-level-2').hide('slow', function () {
                        $(modalId + ' #filter-level-2').val('none');
                        $(modalId + ' #filter-level-2').empty();
                        secondFilterValue = 'none';
                        secondFilterName = 'none';
                    });
                }

                if (third) {
                    $(modalId + ' #box-filter-level-3').hide('slow', function () {
                        $(modalId + ' #filter-level-3').val('none');
                        $(modalId + ' #filter-level-3').empty();
                        thirdFilterValue = 'none';
                        thirdFilterName = 'none';
                    });
                }
                displayFilterCreateButton();
            }

            function updateIdsArray(type)
            {
                switch (type) {
                    case 'group':
                        groupIds = [];

                        for (var key in groupFilters) {

                            if (groupFilters[key] !== null) {
                                groupIds.push(parseInt(key));
                            }
                        }
                        userPicker.parameters.groupIds = groupIds;
                        break;

                    case 'role':
                        roleIds = [];

                        for (var key in roleFilters) {

                            if (roleFilters[key] !== null) {
                                roleIds.push(parseInt(key));
                            }
                        }
                        userPicker.parameters.roleIds = roleIds;
                        break;

                    case 'workspace':
                        workspaceIds = [];

                        for (var key in workspaceFilters) {

                            if (workspaceFilters[key] !== null) {
                                workspaceIds.push(parseInt(key));
                            }
                        }
                        userPicker.parameters.workspaceIds = workspaceIds;
                        break;

                    case 'user':
                        userIds = [];

                        for (var key in selectedUsers) {

                            if (selectedUsers[key] !== null) {
                                userIds.push({
                                    id: parseInt(key),
                                    name: selectedUsers[key]
                                });
                            }
                        }
                        break;
                    default:
                        break
                }
            }

            function createFilter()
            {
                switch (filterType) {
                    case 'group':
                        var groupId = parseInt(secondFilterValue);

                        if (groupFilters[groupId] === undefined || groupFilters[groupId] === null) {
                            groupFilters[groupId] = secondFilterName;
                            updateIdsArray('group');
                            var filterElement =
                                '<li class="filter-element" data-filter-type="group" data-filter-value="' +
                                groupId +
                                '">' +
                                '<span class="label label-info">' +
                                secondFilterName +
                                ' <i class="fa fa-times-circle delete-filter-btn pointer-hand"></i>' +
                                '</span></li>';
                            $(modalId + ' #filters-list-box').append(filterElement);
                        }
                        break;

                    case 'role':
                        var roleId = parseInt(secondFilterValue);

                        if (roleFilters[roleId] === undefined || roleFilters[roleId] === null) {
                            roleFilters[roleId] = secondFilterName;
                            updateIdsArray('role');
                            var filterElement =
                                '<li class="filter-element" data-filter-type="role" data-filter-value="' +
                                roleId +
                                '">' +
                                '<span class="label label-success">' +
                                secondFilterName +
                                ' <i class="fa fa-times-circle delete-filter-btn pointer-hand"></i>' +
                                '</span></li>';
                            $(modalId + ' #filters-list-box').append(filterElement);
                        }
                        break;

                    case 'workspace':

                        if (thirdFilterValue === 'none') {
                            var workspaceId = parseInt(secondFilterValue);

                            if (workspaceFilters[workspaceId] === undefined || workspaceFilters[workspaceId] === null) {
                                workspaceFilters[workspaceId] = secondFilterName;
                                updateIdsArray('workspace');
                                var filterElement =
                                    '<li class="filter-element" data-filter-type="workspace" data-filter-value="' +
                                    workspaceId +
                                    '">' +
                                    '<span class="label label-danger">' +
                                    secondFilterName +
                                    ' <i class="fa fa-times-circle delete-filter-btn pointer-hand"></i>' +
                                    '</span></li>';
                                $(modalId + ' #filters-list-box').append(filterElement);
                            }
                        } else {
                            var roleId = parseInt(thirdFilterValue);

                            if (roleFilters[roleId] === undefined || roleFilters[roleId] === null) {
                                var roleFilterName = thirdFilterName +
                                    ' (' +
                                    secondFilterName +
                                    ')';
                                roleFilters[roleId] = roleFilterName;
                                updateIdsArray('role');
                                var filterElement =
                                    '<li class="filter-element" data-filter-type="role" data-filter-value="' +
                                    roleId +
                                    '">' +
                                    '<span class="label label-success">' +
                                    roleFilterName +
                                    ' <i class="fa fa-times-circle delete-filter-btn pointer-hand"></i>' +
                                    '</span>' +
                                    '</li>';
                                $(modalId + ' #filters-list-box').append(filterElement);
                            }
                        }
                        break;

                    default:
                        break;
                }
                updateFiltersBadge();
            }

            function deleteFilter(type, value)
            {
                switch (type) {
                    case 'group':
                        groupFilters[value] = null;
                        updateIdsArray('group');
                        break;

                    case 'role':
                        roleFilters[value] = null;
                        updateIdsArray('role');
                        break;

                    case 'workspace':
                        workspaceFilters[value] = null;
                        updateIdsArray('workspace');
                        break;

                    default:
                        break;
                }
                updateFiltersBadge();
            }

            function checkSelectedUsers()
            {
                for (var i = 0; i < userIds.length; i++) {
                    $(modalId + ' #picker-user-chk-' + userIds[i]['id']).prop('checked', true);
                }
            }

            function uncheckUser(userId)
            {
                $(modalId + ' #picker-user-chk-' + userId).prop('checked', false);
            }

            function updateSelectedUsersBadge()
            {
                var nbUsers = userIds.length;
                $(modalId + ' #selected-users-box-badge').html(nbUsers);

                if (nbUsers === 0) {
                    $(modalId + ' #picker-no-user-alert').show('slow', function () {});
                } else {
                    $(modalId + ' #picker-no-user-alert').hide('slow', function () {});
                }
            }

            function updateFiltersBadge()
            {
                var nbFilters = groupIds.length + roleIds.length + workspaceIds.length;
                $(modalId + ' #filters-box-badge').html(nbFilters);

                if (nbFilters === 0) {
                    $(modalId + ' #picker-no-filter-alert').show('slow', function () {});
                } else {
                    $(modalId + ' #picker-no-filter-alert').hide('slow', function () {});
                }
            }

            function addUserToSelectedUsersBox(userId, name)
            {
                $(modalId + ' #selected-user-label-' + userId).remove();
                var element =
                    '<li class="user-element" id="selected-user-label-' +
                    userId +
                    '">' +
                    '<span class="label label-primary">' +
                    name +
                    ' <i class="fa fa-times-circle remove-selected-user-btn pointer-hand" data-user-id="' +
                    userId +
                    '"></i>' +
                    '</span></li>';
                $(modalId + ' #selected-users-list-box').append(element);
            }

            function removeUserFromSelectedUsersBox(userId)
            {
                $(modalId + ' #selected-user-label-' + userId).remove();
            }

            function emptySelectedUsersBox()
            {
                $(modalId + ' #selected-users-list-box').empty();
            }

            function refreshUsersList()
            {
                var route = currentSearch === '' ?
                    Routing.generate(
                        'claro_users_list_for_user_picker',
                        {
                            'max': currentMax,
                            'orderedBy': currentOrderedBy,
                            'order': currentOrder,
                            'mode': userPicker.multiple,
                            'showAllUsers': userPicker.showAllUsers,
                            'showId': userPicker.showId,
                            'showPicture': userPicker.showPicture,
                            'showUsername': userPicker.showUsername,
                            'showMail': userPicker.showMail,
                            'showCode': userPicker.showCode,
                            'showGroups': userPicker.showGroups,
                            'showPlatformRoles': userPicker.showPlatformRoles,
                            'attachName': userPicker.attachName,
                            'filterAdminOrgas': userPicker.filterAdminOrgas
                        }
                    ) :
                    Routing.generate(
                        'claro_searched_users_list_for_user_picker',
                        {
                            'search': currentSearch,
                            'max': currentMax,
                            'orderedBy': currentOrderedBy,
                            'order': currentOrder,
                            'mode': userPicker.multiple,
                            'showAllUsers': userPicker.showAllUsers,
                            'showId': userPicker.showId,
                            'showPicture': userPicker.showPicture,
                            'showUsername': userPicker.showUsername,
                            'showMail': userPicker.showMail,
                            'showCode': userPicker.showCode,
                            'showGroups': userPicker.showGroups,
                            'showPlatformRoles': userPicker.showPlatformRoles,
                            'attachName': userPicker.attachName,
                            'filterAdminOrgas': userPicker.filterAdminOrgas
                        }
                    );
                route += '?' + $.param(userPicker.parameters);

                $.ajax({
                    url: route,
                    type: 'GET',
                    success: function (datas) {
                        $(modalId + ' #user-picker-users-list').html(datas);
                        checkSelectedUsers();
                    }
                });
            }

            function initializeSelectedUsers()
            {
                var matchedSelected = (userPicker.parameters['selectedUserIds'].length === selectedUserNames.length);

                for (var i = 0; i < userPicker.parameters['selectedUserIds'].length; i++) {
                    var name = matchedSelected ? selectedUserNames[i] : '???';
                    selectedUsers[userPicker.parameters['selectedUserIds'][i]] = name;
                    addUserToSelectedUsersBox(userPicker.parameters['selectedUserIds'][i], name);
                }
                updateIdsArray('user');
                updateSelectedUsersBadge();
                checkSelectedUsers();
            }

            modalElement.on('click', 'a', function (event) {
                event.preventDefault();
                var element = event.currentTarget;
                var route = $(element).attr('href');
                route += '?' + $.param(userPicker.parameters);

                $.ajax({
                    url: route,
                    type: 'GET',
                    success: function (datas) {
                        $(modalId + ' #user-picker-users-list').html(datas);
                        checkSelectedUsers();
                    }
                });
            });

            modalElement.on('click', '#search-user-btn', function () {
                currentSearch = $(modalId + ' #search-user-input').val();
                refreshUsersList();
            });

           modalElement.on('keypress', '#search-user-input', function(e) {

                if (e.keyCode === 13) {
                    e.preventDefault();
                    currentSearch = $(this).val();
                    refreshUsersList();
                }
            });

            modalElement.on('change', '#max-select', function() {
                currentMax = $(this).val();
                refreshUsersList();
            });

            modalElement.on('change', '#filter-level-1', function() {
                filterType = $(this).val();
                displaySecondFilter();
                displayThirdFilter();
                displayFilterCreateButton();
            });

            modalElement.on('change', '#filter-level-2', function() {
                secondFilterValue = $(this).val();
                secondFilterName = $(modalId + ' #' + filterType + '-' + secondFilterValue).html();
                displayThirdFilter();
                displayFilterCreateButton();
            });

            modalElement.on('change', '#filter-level-3', function () {
                thirdFilterValue = $(this).val();
                thirdFilterName = $(modalId + ' #ws-role-' + thirdFilterValue).html();
            });

            modalElement.on('click', '#filter-create-btn', function () {
                createFilter();
                resetFilters(true, true, true);
                refreshUsersList();
            });

            modalElement.on('click', '.delete-filter-btn', function () {
                var parentElement = $(this).parents('.filter-element');
                var type = parentElement.data('filter-type');
                var value = parentElement.data('filter-value');
                deleteFilter(type, parseInt(value));
                parentElement.remove();
                refreshUsersList();
            });

            modalElement.on('click', '.picker-user-chk', function () {
                var userId = $(this).val();

                if (userPicker.multiple === 'multiple') {

                    if ($(this).prop('checked')) {
                        var firstName = $(this).data('user-first-name');
                        var lastName = $(this).data('user-last-name');

                        if (parseInt(userPicker.showUsername) === 1) {
                            var username = $(this).data('user-username');
                            selectedUsers[userId] = firstName + ' ' + lastName + ' (' + username + ')';
                        } else {
                            selectedUsers[userId] = firstName + ' ' + lastName;
                        }
                        addUserToSelectedUsersBox(userId, selectedUsers[userId]);
                    } else {
                        selectedUsers[userId] = null;
                        removeUserFromSelectedUsersBox(userId);
                    }
                } else if (userPicker.multiple === 'single') {
                    var firstName = $(this).data('user-first-name');
                    var lastName = $(this).data('user-last-name');
                    emptySelectedUsersBox();
                    selectedUsers = [];

                    if (parseInt(userPicker.showUsername) === 1) {
                        var username = $(this).data('user-username');
                        selectedUsers[userId] = firstName + ' ' + lastName + ' (' + username + ')';
                    } else {
                        selectedUsers[userId] = firstName + ' ' + lastName;
                    }
                    addUserToSelectedUsersBox(userId, selectedUsers[userId]);
                }
                updateIdsArray('user');
                updateSelectedUsersBadge();
            });

            modalElement.on('click', '#picker-all-users-chk', function () {
                if ($(this).prop('checked')) {
                    $(modalId + ' .picker-user-chk').each(function () {
                        $(this).prop('checked', true);
                        var userId = $(this).val();
                        var firstName = $(this).data('user-first-name');
                        var lastName = $(this).data('user-last-name');
                        var username = $(this).data('user-username');
                        selectedUsers[userId] = firstName + ' ' + lastName + ' (' + username + ')';
                        addUserToSelectedUsersBox(userId, selectedUsers[userId]);
                    });
                } else {
                    $(modalId + ' .picker-user-chk').each(function () {
                        $(this).prop('checked', false);
                        var userId = $(this).val();
                        selectedUsers[userId] = null;
                        removeUserFromSelectedUsersBox(userId);
                    });
                }
                updateIdsArray('user');
                updateSelectedUsersBadge();
            });

            modalElement.on('click', '.picker-user-select', function () {
                var userId = $(this).data('user-id');
                $(modalId + ' #picker-user-chk-' + userId).trigger('click');
            });

            modalElement.on('click', '.remove-selected-user-btn', function () {
                var userId = $(this).data('user-id');
                selectedUsers[userId] = null;
                removeUserFromSelectedUsersBox(userId);
                uncheckUser(userId);
                updateIdsArray('user');
                updateSelectedUsersBadge();
            });

            modalElement.on('click', '.submit', function () {

                if (userPicker.returnDatas) {
                    var ids = [];

                    for (var i = 0; i < userIds.length; i++) {
                        ids[i] = parseInt(userIds[i]['id']);
                    }

                    var params = {};
                    params.userIds = ids;
                    var route = Routing.generate('claro_users_infos_request');
                    route += '?' + $.param(params);

                    $.ajax({
                        url: route,
                        type: 'GET',
                        success: function (datas) {
                            (datas.length > 0) ?
                                userPicker.callBack(datas) :
                                userPicker.callBack(null);
                        }
                    });
                } else {

                    if (userPicker.multiple === 'multiple') {
                        var ids = [];
                        var names = [];

                        for (var i = 0; i < userIds.length; i++) {
                            ids[i] = parseInt(userIds[i]['id']);
                            names[i] = userIds[i]['name'];
                        }
                        $('#user-picker-input-' + userPicker.pickerName).val(ids).trigger('input');
                        $('#user-picker-input-view-' + userPicker.pickerName).val(names).trigger('input');

                        (ids.length > 0) ?
                            userPicker.callBack(ids) :
                            userPicker.callBack(null);
                    } else if (userPicker.multiple === 'single') {

                        if (userIds.length > 0) {
                            $('#user-picker-input-' + userPicker.pickerName).val(parseInt(userIds[0]['id'])).trigger('input');
                            $('#user-picker-input-view-' + userPicker.pickerName).val(userIds[0]['name']).trigger('input');
                            userPicker.callBack(userIds[0]['id']);
                        } else {
                            $('#user-picker-input-' + userPicker.pickerName).val(null).trigger('input');
                            $('#user-picker-input-view-' + userPicker.pickerName).val(null).trigger('input');
                            userPicker.callBack(null);
                        }
                    }
                }
                modalElement.modal('hide');
            });

            initializeSelectedUsers();
        }
    });
};
