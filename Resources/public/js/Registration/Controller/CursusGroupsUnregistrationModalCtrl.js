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

    angular.module('CursusRegistrationModule').controller('CursusGroupsUnregistrationModalCtrl', [
        '$http',
        '$uibModalStack',
        'cursusGroupsIdsTxt',
        'callBack',
        function ($http, $uibModalStack, cursusGroupsIdsTxt, callBack) {
            var cursusGroupsIdsTxt = cursusGroupsIdsTxt;
            var callBack = callBack;
            
            this.closeModal = function () {
                $uibModalStack.dismissAll();
            };
            
            this.confirmModal = function () {
                var route = Routing.generate(
                    'api_delete_cursus_groups',
                    {cursusGroupsIdsTxt: cursusGroupsIdsTxt}
                );
                $http.delete(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        var cursusGroupsIds = cursusGroupsIdsTxt.split(',');
                        
                        for (var i = 0; i < cursusGroupsIds.length; i++) {
                            cursusGroupsIds[i] = parseInt(cursusGroupsIds[i]);
                        }
                        callBack(cursusGroupsIds);
                        $uibModalStack.dismissAll();
                    }
                });
            };
        }
    ]);
})();