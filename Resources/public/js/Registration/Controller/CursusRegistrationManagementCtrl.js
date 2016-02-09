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

    angular.module('CursusRegistrationModule').controller('CursusRegistrationManagementCtrl', [
        '$routeParams',
        '$http',
        '$uibModal',
        function ($routeParams, $http, $uibModal) {
            var vm = this;
            var unlockedCursusTxt = '';
            var usersIdsTxt;
            this.currentCursusId = $routeParams['cursusId'];
            this.hierarchy = [];
            this.lockedHierarchy = [];
            this.unlockedCursus = [];
            this.cursusGroups = [];
            this.cursusUsers = [];
            this.selectedUsers = {};
            this.selectedCursusGroups = {};
            this.allUsers = false;
            this.allGroups = false;

            var userPickerCallBack = function (datas) {
                
                if (datas === null) {
                    usersIdsTxt = '0';
                } else {
                    usersIdsTxt = '';
                    
                    for (var i = 0; i < datas.length; i++) {
                        usersIdsTxt += datas[i]['id'] + ',';
                    }
                    var length = usersIdsTxt.length;
                    
                    if (length > 0) {
                        usersIdsTxt = usersIdsTxt.substr(0, length - 1)
                    }
                }
                $uibModal.open({
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Partial/cursus_registration_sessions_modal.html',
                    controller: 'CursusRegistrationSessionsModalCtrl',
                    controllerAs: 'crsmc',
                    resolve: {
                        sourceId: function () { return usersIdsTxt; },
                        sourceType: function () { return 'user'; },
                        cursusIdsTxt: function () { return unlockedCursusTxt; }
                    }
                });
            };

            var usersColumns = [
                {
                    name: 'checkboxes',
                    headerRenderer: function () {
                        
                        return '<span><input type="checkbox" ng-click="crmc.toggleAllUsers()"></span>';
                    },
                    cellRenderer: function(scope) {
                        
                        return '<span><input type="checkbox" ng-model="crmc.selectedUsers[' +
                            scope.$row['userId'] +
                            ']"></span>';
                    }
                },
                {
                    name: 'firstName',
                    prop: 'firstName',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('first_name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'lastName',
                    prop: 'lastName',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('last_name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'username',
                    prop: 'username',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('username', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'registration_date',
                    prop: 'registrationDate',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('registration_date', {}, 'cursus') + '</b>';
                    },
                    cellRenderer: function(scope) {
                        
                        return '<span>' + scope.$row['registrationDate']['date'] + '</span>';
                    }
                },
                {
                    name: 'actions',
                    headerRenderer: function () {
                        
                        return '<button class="btn btn-default btn-sm"' +
                            ' ng-click="crmc.unregisterSelectedUsers()"' +
                            ' ng-disabled="!crmc.isUserSelected()">' +
                            translator.trans('unregister_selected_users', {}, 'cursus') +
                            '</button>';
                    },
                    cellRenderer: function (scope) {
                        
                        return '<button class="btn btn-danger btn-sm" ng-click="crmc.unregisterUser(' +
                            scope.$row['id'] +
                            ', \'' +
                            scope.$row['firstName']  + ' ' + scope.$row['lastName']  + ' (' + scope.$row['username'] + ')' +
                            '\')">' +
                            translator.trans('unregister', {}, 'cursus') +
                            '</button>';
                    }
                }
            ];

            var groupsColumns = [
                {
                    name: 'checkboxes',
                    headerRenderer: function () {
                        
                        return '<span><input type="checkbox" ng-click="crmc.toggleAllGroups()"></span>';
                    },
                    cellRenderer: function(scope) {
                        
                        return '<span><input type="checkbox" ng-model="crmc.selectedCursusGroups[' +
                            scope.$row['id'] +
                            ']"></span>';
                    }
                },
                {
                    name: 'name',
                    prop: 'groupName',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'registration_date',
                    prop: 'registrationDate',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('registration_date', {}, 'cursus') + '</b>';
                    },
                    cellRenderer: function(scope) {
                        
                        return '<span>' + scope.$row['registrationDate']['date'] + '</span>';
                    }
                },
                {
                    name: 'actions',
                    headerRenderer: function () {
                        
                        return '<button class="btn btn-default btn-sm"' +
                            ' ng-click="crmc.unregisterSelectedGroups()"' +
                            ' ng-disabled="!crmc.isGroupSelected()">' +
                            translator.trans('unregister_selected_groups', {}, 'cursus') +
                            '</button>';
                    },
                    cellRenderer: function (scope) {
                        
                        return '<button class="btn btn-danger btn-sm" ng-click="crmc.unregisterGroup(' +
                            scope.$row['id'] + ', \'' + scope.$row['groupName'] +
                            '\')">' +
                            translator.trans('unregister', {}, 'cursus') +
                            '</button>';
                    }
                }
            ];
            
            this.dataGroupsTableOptions = {
                scrollbarV: false,
                columnMode: 'force',
                headerHeight: 50,
                selectable: true,
                multiSelect: true,
                checkboxSelection: true,
                resizable: true,
                columns: groupsColumns
            };
            
            this.dataUsersTableOptions = {
                scrollbarV: false,
                columnMode: 'force',
                headerHeight: 50,
                selectable: true,
                multiSelect: true,
                checkboxSelection: true,
                resizable: true,
                columns: usersColumns
            };
            
            this.registerGroups = function () {
                $uibModal.open({
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Partial/cursus_groups_list_registration_modal.html',
                    controller: 'CursusGroupsListRegistrationModalCtrl',
                    controllerAs: 'cglrmc',
                    resolve: {
                        cursusId: function () { return vm.currentCursusId; },
                        cursusIdsTxt: function () { return unlockedCursusTxt; }
                    }
                });
            };
            
            this.registerUsers = function () {
                var usersIds = [];
                
                for (var i = 0; i < vm.cursusUsers.length; i++) {
                    usersIds.push(vm.cursusUsers[i]['userId']);
                }
                var userPicker = new UserPicker();
                var config = {
                    picker_name: 'cursus-registration-users-picker',
                    picker_title: Translator.trans('register_users_to_cursus', {}, 'cursus'),
                    multiple: true,
                    blacklist: usersIds,
                    return_datas: true,
                    attach_name: false
                };
                userPicker.configure(config, userPickerCallBack);
                userPicker.open();
            };
            
            this.unregisterGroup = function (cursusGroupId, groupName) {
                $uibModal.open({
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Partial/cursus_group_unregistration_modal.html',
                    controller: 'CursusGroupUnregistrationModalCtrl',
                    controllerAs: 'cgumc',
                    resolve: {
                        cursusGroupId: function () { return cursusGroupId; },
                        groupName: function () { return groupName; },
                        callBack: function () { return vm.removeCursusGroup; }
                    }
                });
            };
            
            this.unregisterSelectedGroups = function () {
                var cursusGroupsIdsTxt = '';
                
                for (var cursusGroupId in vm.selectedCursusGroups) {
                    
                    if (vm.selectedCursusGroups[cursusGroupId]) {
                        cursusGroupsIdsTxt += cursusGroupId + ',';
                    }
                }
                var length = cursusGroupsIdsTxt.length;
                
                if (length > 0) {
                    cursusGroupsIdsTxt = cursusGroupsIdsTxt.substr(0, length - 1);
                }
                $uibModal.open({
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Partial/cursus_groups_unregistration_modal.html',
                    controller: 'CursusGroupsUnregistrationModalCtrl',
                    controllerAs: 'cgumc',
                    resolve: {
                        cursusGroupsIdsTxt: function () { return cursusGroupsIdsTxt; },
                        callBack: function () { return vm.removeCursusGroups; }
                    }
                });
            };
            
            this.removeCursusGroup = function (cursusGroupId) {
                
                for (var i = 0; i < vm.cursusGroups.length; i++) {
                    
                    if (vm.cursusGroups[i]['id'] === cursusGroupId) {
                        vm.cursusGroups.splice(i, 1);
                        break;
                    }
                }
                updateCursusUsers();
            };
            
            this.removeCursusGroups = function (cursusGroupIds) {
                
                for (var i = vm.cursusGroups.length - 1; i >= 0; i--) {
                    
                    if (cursusGroupIds.indexOf(vm.cursusGroups[i]['id']) >= 0) {
                        vm.cursusGroups.splice(i, 1);
                    }
                }
                updateCursusUsers();
            };
            
            this.removeCursusUsers = function (userIds) {
                
                for (var i = vm.cursusUsers.length - 1; i >= 0; i--) {
                    
                    if (userIds.indexOf(vm.cursusUsers[i]['userId']) >= 0) {
                        vm.cursusUsers.splice(i, 1);
                    }
                }
            };
            
            this.unregisterUser = function (cursusUserId, name) {
                $uibModal.open({
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Partial/cursus_user_unregistration_modal.html',
                    controller: 'CursusUserUnregistrationModalCtrl',
                    controllerAs: 'cuumc',
                    resolve: {
                        cursusUserId: function () { return cursusUserId; },
                        name: function () { return name; },
                        callBack: function () { return vm.removeCursusUser; }
                    }
                });
            };
            
            this.unregisterSelectedUsers = function () {
                var usersIdsTxt = '';
                
                for (var userId in vm.selectedUsers) {
                    
                    if (vm.selectedUsers[userId]) {
                        usersIdsTxt += userId + ',';
                    }
                }
                var length = usersIdsTxt.length;
                
                if (length > 0) {
                    usersIdsTxt = usersIdsTxt.substr(0, length - 1);
                }
                $uibModal.open({
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Partial/cursus_users_unregistration_modal.html',
                    controller: 'CursusUsersUnregistrationModalCtrl',
                    controllerAs: 'cuumc',
                    resolve: {
                        cursusId: function () { return vm.currentCursusId; },
                        usersIdsTxt: function () { return usersIdsTxt; },
                        callBack: function () { return vm.removeCursusUsers; }
                    }
                });
            };
            
            this.removeCursusUser = function (cursusUserId) {
                
                for (var i = 0; i < vm.cursusUsers.length; i++) {
                    
                    if (vm.cursusUsers[i]['id'] === cursusUserId) {
                        vm.cursusUsers.splice(i, 1);
                        break;
                    }
                }
            };
            
            this.isGroupSelected = function () {
                var selected = false;
                
                for (var cursusGroupId in vm.selectedCursusGroups) {
                    
                    if (vm.selectedCursusGroups[cursusGroupId]) {
                        selected = true;
                        break;
                    }
                }
                
                return selected;
            };
            
            this.isUserSelected = function () {
                var selected = false;
                
                for (var userId in vm.selectedUsers) {
                    
                    if (vm.selectedUsers[userId]) {
                        selected = true;
                        break;
                    }
                }
                
                return selected;
            };
            
            this.toggleAllGroups = function () {
                vm.allGroups = !vm.allGroups;
                
                for (var cursusGroupId in vm.selectedCursusGroups) {
                    vm.selectedCursusGroups[cursusGroupId] = vm.allGroups;
                }
            };
            
            this.toggleAllUsers = function () {
                vm.allUsers = !vm.allUsers;
                
                for (var userId in vm.selectedUsers) {
                    vm.selectedUsers[userId] = vm.allUsers;
                }
            };
            
            function initialize() {
                var route = Routing.generate('api_get_datas_for_cursus_registration', {cursus: vm.currentCursusId});
                $http.get(route).then(function (datas) {
                    var data = datas['data'];
                    vm.hierarchy = data['hierarchy'];
                    vm.lockedHierarchy = data['lockedHierarchy'];
                    vm.unlockedCursus = data['unlockedCursus'];
                    vm.cursusGroups = data['cursusGroups'];
                    vm.cursusUsers = data['cursusUsers'];

                    for (var i = 0; i < vm.unlockedCursus.length; i++) {
                        unlockedCursusTxt += vm.unlockedCursus[i];
                        
                        if (i < vm.unlockedCursus.length - 1) {
                            unlockedCursusTxt += ',';
                        }
                    }
                    
                    for (var i = 0; i < vm.cursusGroups.length; i++) {
                        var cursusGroupId = vm.cursusGroups[i]['id'];
                        vm.selectedCursusGroups[cursusGroupId] = false;
                    }
                    
                    for (var i = 0; i < vm.cursusUsers.length; i++) {
                        var userId = vm.cursusUsers[i]['userId'];
                        vm.selectedUsers[userId] = false;
                    }
                });
            };
            
            function updateCursusUsers()
            {
                var route = Routing.generate(
                    'api_get_cursus_users_for_cursus_registration',
                    {cursus: vm.currentCursusId}
                );
                $http.get(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        vm.cursusUsers = datas['data'];
                    }
                });
            }
            
            initialize();
        }
    ]);
})();