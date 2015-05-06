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
    
    var currentSearch = $('#user-picker-datas-box').data('search');
    var currentMax = $('#user-picker-datas-box').data('max');
    var currentOrderedBy = $('#user-picker-datas-box').data('ordered-by');
    var currentOrder = $('#user-picker-datas-box').data('order');
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
    var parameters = {};
    parameters.groupIds = groupIds;
    parameters.roleIds = roleIds;
    parameters.workspaceIds = workspaceIds;
    
    function displaySecondFilter()
    {
        if (filterType === 'none') {
            resetFilters(true, true, true);
        } else {
            $('#box-filter-level-2').show('slow', function () {
                secondFilterValue = 'none';
                thirdFilterValue = 'none';
                $('#filter-level-2').val('none');
                $('#filter-level-3').val('none');
                
                $.ajax({
                    url: Routing.generate(
                        'claro_filters_list_for_user_picker',
                        {'filterType': filterType}
                    ),
                    type: 'GET',
                    success: function (datas) {
                        $('#filter-level-2').empty();
                        var option = '<option value="none" id="' + filterType + '-none">--- ' +
                            Translator.trans('select_a_' + filterType, {}, 'platform') +
                            ' ---</option>';
                        $('#filter-level-2').append(option);
                        
                        for (var i = 0; i < datas.length; i++) {
                            option = '<option value="' +
                                datas[i]['id'] +
                                '" id="' + filterType + '-' +
                                datas[i]['id'] +
                                '">' +
                                datas[i]['name']
                                '</option>';
                            $('#filter-level-2').append(option);
                        }
                        $('#box-filter-level-2').show('slow', function () {
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
                    $('#filter-level-3').empty();
                    var option = '<option value="none" id="ws-role-none">--- ' +
                        Translator.trans('all_roles', {}, 'platform') +
                        ' ---</option>';
                    $('#filter-level-3').append(option);

                    for (var i = 0; i < datas.length; i++) {
                        option = '<option value="' +
                            datas[i]['id'] +
                            '" id="ws-role-' +
                            datas[i]['id'] +
                            '">' +
                            datas[i]['name']
                            '</option>';
                        $('#filter-level-3').append(option);
                    }
                    $('#box-filter-level-3').show('slow', function () {
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
            $('#box-filter-create-btn').show('slow', function () {
                $(this).removeClass('hidden');
            });
        } else {
            $('#box-filter-create-btn').hide('slow');
        }
    }
    
    function resetFilters(first, second, third)
    {
        if (first) {
            $('#filter-level-1').val('none');
            filterType = 'none';
        }
        
        if (second) {
            $('#box-filter-level-2').hide('slow', function () {
                $('#filter-level-2').val('none');
                $('#filter-level-2').empty();
                secondFilterValue = 'none';
                secondFilterName = 'none';
            });
        }
        
        if (third) {
            $('#box-filter-level-3').hide('slow', function () {
                $('#filter-level-3').val('none');
                $('#filter-level-3').empty();
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
                parameters.groupIds = groupIds;
                break;
                
            case 'role':
                roleIds = [];
                
                for (var key in roleFilters) {
                    
                    if (roleFilters[key] !== null) {
                        roleIds.push(parseInt(key));
                    }
                }
                parameters.roleIds = roleIds;
                break;
                
            case 'workspace':
                workspaceIds = [];
                
                for (var key in workspaceFilters) {
                    
                    if (workspaceFilters[key] !== null) {
                        workspaceIds.push(parseInt(key));
                    }
                }
                parameters.workspaceIds = workspaceIds;
                break;
                
            case 'user':
                userIds = [];
                
                for (var key in selectedUsers) {
                    
                    if (selectedUsers[key] !== null) {
                        userIds.push(parseInt(key));
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
                    $('#filters-list-box').append(filterElement);
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
                    $('#filters-list-box').append(filterElement);
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
                        $('#filters-list-box').append(filterElement);
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
                        $('#filters-list-box').append(filterElement);
                    }
                }
                break;
            
            default:
                break;
        }
        displayFilterCreationBox(0);
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
            $('#picker-user-chk-' + userIds[i]).prop('checked', true);
        }
    }
    
    function uncheckUser(userId)
    {
        $('#picker-user-chk-' + userId).prop('checked', false);
    }
    
    function updateSelectedUsersBadge()
    {
        var nbUsers = userIds.length;
        $('#selected-users-box-badge').html(nbUsers);
        
        if (nbUsers === 0) {
            $('#no-user-alert').show('slow', function () {});
        } else {
            $('#no-user-alert').hide('slow', function () {});
        }
    }
    
    function updateFiltersBadge()
    {
        var nbFilters = groupIds.length + roleIds.length + workspaceIds.length;
        $('#filters-box-badge').html(nbFilters);
        
        if (nbFilters === 0) {
            $('#no-filter-alert').show('slow', function () {});
        } else {
            $('#no-filter-alert').hide('slow', function () {});
        }
    }
    
    function displayFilterCreationBox(mode)
    {
        if (mode === 1) {
            $('#show-filter-creation-box-btn').hide('slow', function () {});
            $('#filter-creation-box').show('slow', function () {
                $('#filter-creation-box').removeClass('hidden');
            });
        } else {
            $('#filter-creation-box').hide('slow', function () {});
            $('#show-filter-creation-box-btn').show('slow', function () {
                $('#show-filter-creation-box-btn').removeClass('hidden');
            });
        }
    }
    
    function addUserToSelectedUsersBox(userId, name)
    {
        $('#selected-user-label-' + userId).remove();
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
        $('#selected-users-list-box').append(element);
    }
    
    function removeUserFromSelectedUsersBox(userId)
    {
        $('#selected-user-label-' + userId).remove();
    }
    
    function refreshUsersList()
    {
        var route = currentSearch === '' ?
            Routing.generate(
                'claro_users_list_for_user_picker',
                {
                    'max': currentMax,
                    'orderedBy': currentOrderedBy,
                    'order': currentOrder
                }
            ) :
            Routing.generate(
                'claro_searched_users_list_for_user_picker',
                {
                    'search': currentSearch,
                    'max': currentMax,
                    'orderedBy': currentOrderedBy,
                    'order': currentOrder
                }
            );
        route += '?' + $.param(parameters);

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#user-picker-users-list').html(datas);
                checkSelectedUsers();
            }
        });
    }
    
    $('#user-picker-modal').on('click', 'a', function (event) {
        event.preventDefault();
        var element = event.currentTarget;
        var route = $(element).attr('href');
        route += '?' + $.param(parameters);

        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                $('#user-picker-users-list').html(datas);
                checkSelectedUsers();
            }
        });
    });

    $('#user-picker-modal').on('click', '#search-user-btn', function () {
        currentSearch = $('#search-user-input').val();
        refreshUsersList();
    });

    $('#user-picker-modal').on('keypress', '#search-user-input', function(e) {
        
        if (e.keyCode === 13) {
            e.preventDefault();
            currentSearch = $(this).val();
            refreshUsersList();
        }
    });
    
    $('#user-picker-modal').on('change', '#max-select', function () {
        currentMax = $(this).val();
        refreshUsersList();
    });
    
    $('#user-picker-modal').on('click', '#show-filter-creation-box-btn', function () {
        displayFilterCreationBox(1);
    });
    
    $('#user-picker-modal').on('change', '#filter-level-1', function () {
        filterType = $(this).val();
        displaySecondFilter();
        displayThirdFilter();
        displayFilterCreateButton();
    });
    
    $('#user-picker-modal').on('change', '#filter-level-2', function () {
        secondFilterValue = $(this).val();
        secondFilterName = $('#' + filterType + '-' + secondFilterValue).html();
        displayThirdFilter();
        displayFilterCreateButton();
    });
    
    $('#user-picker-modal').on('change', '#filter-level-3', function () {
        thirdFilterValue = $(this).val();
        thirdFilterName = $('#ws-role-' + thirdFilterValue).html();
    });
    
    $('#user-picker-modal').on('click', '#filter-create-btn', function () {
        createFilter();
        resetFilters(true, true, true);
        refreshUsersList();
    });
    
    $('#user-picker-modal').on('click', '.delete-filter-btn', function () {
        var parentElement = $(this).parents('.filter-element');
        var type = parentElement.data('filter-type');
        var value = parentElement.data('filter-value');
        deleteFilter(type, parseInt(value));
        parentElement.remove();
        refreshUsersList();
    });
    
    $('#user-picker-modal').on('click', '.picker-user-chk', function () {
        var userId = $(this).val();
        
        if ($(this).prop('checked')) {
            var firstName = $(this).data('user-first-name');
            var lastName = $(this).data('user-last-name');
            var username = $(this).data('user-username');
            selectedUsers[userId] = firstName + ' ' + lastName + ' (' + username + ')';
            addUserToSelectedUsersBox(userId, selectedUsers[userId]);
        } else {
            selectedUsers[userId] = null;
            removeUserFromSelectedUsersBox(userId);
        }
        updateIdsArray('user');
        updateSelectedUsersBadge();
    });
    
    $('#selected-users-list-box').on('click', '.remove-selected-user-btn', function () {
        var userId = $(this).data('user-id');
        selectedUsers[userId] = null;
        removeUserFromSelectedUsersBox(userId);
        uncheckUser(userId);
        updateIdsArray('user');
        updateSelectedUsersBadge();
    });
})();