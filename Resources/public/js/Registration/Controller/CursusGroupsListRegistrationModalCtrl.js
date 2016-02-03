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
        '$uibModalStack',
        'cursusId',
        function ($http, $uibModalStack, cursusId) {
            var vm = this;
            this.cursusId = cursusId;
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
            
            function getUnregisteredGroups()
            {
                var route = (vm.search === '') ?
                    Routing.generate(
                        'api_get_unregistered_cursus_groups',
                        {cursus: vm.cursusId}
                    ) :
                    Routing.generate(
                        'api_get_searched_unregistered_cursus_groups',
                        {cursus: vm.cursusId, search: vm.search}
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