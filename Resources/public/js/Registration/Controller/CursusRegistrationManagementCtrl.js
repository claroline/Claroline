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
                    name: 'firstName',
                    prop: 'firstName',
                    isCheckboxColumn: true,
                    headerCheckbox: true,
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
                        
                        return '<button class="btn btn-default btn-sm" ng-click="crmc.unregisterSelectedUsers()">' +
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
                    name: 'name',
                    prop: 'groupName',
                    isCheckboxColumn: true,
                    headerCheckbox: true,
                    headerRenderer: function () {
                        return '<b>' + translator.trans('name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'registration_date',
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
                        
                        return '<button class="btn btn-default btn-sm" ng-click="crmc.unregisterSelectedGroups()">' +
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
                columns: groupsColumns
            };
            
            this.dataUsersTableOptions = {
                scrollbarV: false,
                columnMode: 'force',
                headerHeight: 50,
                selectable: true,
                multiSelect: true,
                checkboxSelection: true,
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
                console.log('Unregister selected groups');
            };
            
            this.removeCursusGroup = function (cursusGroupId) {
                
                for (var i = 0; i < vm.cursusGroups.length; i++) {
                    
                    if (vm.cursusGroups[i]['id'] === cursusGroupId) {
                        vm.cursusGroups.splice(i, 1);
                        break;
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
                console.log('Unregister selected users');
            };
            
            this.removeCursusUser = function (cursusUserId) {
                
                for (var i = 0; i < vm.cursusUsers.length; i++) {
                    
                    if (vm.cursusUsers[i]['id'] === cursusUserId) {
                        vm.cursusUsers.splice(i, 1);
                        break;
                    }
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
                });
            };
            
            initialize();
        }
    ]);
})();