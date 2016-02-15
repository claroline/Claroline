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

    angular.module('CursusRegistrationModule').controller('CursusGroupsListRegistrationModalCtrl', [
        '$http',
        '$uibModal',
        '$uibModalStack',
        'cursusId',
        'cursusIdsTxt',
        function ($http, $uibModal, $uibModalStack, cursusId, cursusIdsTxt) {
            var vm = this;
            var cursusId = cursusId;
            var cursusIdsTxt = cursusIdsTxt;
            this.search = '';
            this.tempSearch = '';
            this.groups = [];
            
            this.closeModal = function () {
                $uibModalStack.dismissAll();
            };
            
            this.searchGroups = function () {
                vm.search = vm.tempSearch;
                getUnregisteredGroups();
            };

            var groupsColumns = [
                {
                    name: 'name',
                    prop: 'name',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'actions',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('actions', {}, 'platform') + '</b>';
                    },
                    cellRenderer: function (scope) {
                        
                        return '<button class="btn btn-success btn-sm" ng-click="cglrmc.selectGroupForSessionsValidation(' +
                            scope.$row['id'] +
                            ')">' +
                            translator.trans('register', {}, 'cursus') +
                            '</button>';
                    }
                }
            ];
            
            this.dataTableOptions = {
                scrollbarV: false,
                columnMode: 'force',
                headerHeight: 50,
                selectable: true,
                multiSelect: true,
                checkboxSelection: true,
                columns: groupsColumns
            };
            
            this.selectGroupForSessionsValidation = function (groupId) {
                vm.closeModal();
                $uibModal.open({
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Cursus/Partial/cursus_registration_sessions_modal.html',
                    controller: 'CursusRegistrationSessionsModalCtrl',
                    controllerAs: 'crsmc',
                    resolve: {
                        sourceId: function () { return groupId; },
                        sourceType: function () { return 'group'; },
                        cursusIdsTxt: function () { return cursusIdsTxt; }
                    }
                });
            };
            
            function getUnregisteredGroups()
            {
                var route = (vm.search === '') ?
                    Routing.generate(
                        'api_get_unregistered_cursus_groups',
                        {cursus: cursusId}
                    ) :
                    Routing.generate(
                        'api_get_searched_unregistered_cursus_groups',
                        {cursus: cursusId, search: vm.search}
                    );
                $http.get(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        vm.groups = datas['data'];
                    }
                });
            }
            
            getUnregisteredGroups();
        }
    ]);
})();