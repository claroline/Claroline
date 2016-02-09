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

    angular.module('CursusRegistrationModule').controller('CursusUsersUnregistrationModalCtrl', [
        '$http',
        '$uibModalStack',
        'cursusId',
        'usersIdsTxt',
        'callBack',
        function ($http, $uibModalStack, cursusId, usersIdsTxt, callBack) {
            var cursusId = cursusId;
            var usersIdsTxt = usersIdsTxt;
            var callBack = callBack;
            
            this.closeModal = function () {
                $uibModalStack.dismissAll();
            };
            
            this.confirmModal = function () {
                var route = Routing.generate(
                    'api_delete_cursus_users',
                    {cursus: cursusId, usersIdsTxt: usersIdsTxt}
                );
                $http.delete(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        var usersIds = usersIdsTxt.split(',');
                        
                        for (var i = 0; i < usersIds.length; i++) {
                            usersIds[i] = parseInt(usersIds[i]);
                        }
                        callBack(usersIds);
                        $uibModalStack.dismissAll();
                    }
                });
            };
        }
    ]);
})();