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

    angular.module('CursusRegistrationModule').controller('CursusGroupUnregistrationModalCtrl', [
        '$http',
        '$uibModalStack',
        'cursusGroupId',
        'groupName',
        'callBack',
        function ($http, $uibModalStack, cursusGroupId, groupName, callBack) {
            var vm = this;
            this.cursusGroupId = cursusGroupId;
            this.groupName = groupName;
            this.callBack = callBack;
            
            this.closeModal = function () {
                $uibModalStack.dismissAll();
            };
            
            this.confirmModal = function () {
                var route = Routing.generate('api_delete_cursus_group', {cursusGroup: vm.cursusGroupId});
                $http.delete(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        vm.callBack(vm.cursusGroupId);
                        $uibModalStack.dismissAll();
                    }
                });
            };
        }
    ]);
})();