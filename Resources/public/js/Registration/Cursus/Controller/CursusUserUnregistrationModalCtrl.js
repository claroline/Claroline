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

    angular.module('CursusRegistrationModule').controller('CursusUserUnregistrationModalCtrl', [
        '$http',
        '$uibModalStack',
        'cursusUserId',
        'name',
        'callBack',
        function ($http, $uibModalStack, cursusUserId, name, callBack) {
            var vm = this;
            this.cursusUserId = cursusUserId;
            this.name = name;
            this.callBack = callBack;
            
            this.closeModal = function () {
                $uibModalStack.dismissAll();
            };
            
            this.confirmModal = function () {
                var route = Routing.generate('api_delete_cursus_user', {cursusUser: vm.cursusUserId});
                $http.delete(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        vm.callBack(vm.cursusUserId);
                        $uibModalStack.dismissAll();
                    }
                });
            };
        }
    ]);
})();