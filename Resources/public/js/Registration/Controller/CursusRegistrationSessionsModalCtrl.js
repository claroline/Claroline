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

    angular.module('CursusRegistrationModule').controller('CursusRegistrationSessionsModalCtrl', [
        '$http',
        '$route',
        '$uibModal',
        '$uibModalStack',
        'cursusId',
        'sourceId',
        'sourceType',
        'cursusIdsTxt',
        function ($http, $route, $uibModal, $uibModalStack, sourceId, sourceType, cursusIdsTxt) {
            var vm = this;
            var sourceId = sourceId;
            var sourceType = sourceType;
            var cursusIdsTxt = cursusIdsTxt;
            this.sessionsDatas = [];
            this.selectedSessions = [];
            
            this.closeModal = function () {
                $uibModalStack.dismissAll();
            };
            
            this.confirmModal = function () {
                var route;
                var sessionsIdsTxt = '';
                
                for (var courseId in vm.selectedSessions) {
                    sessionsIdsTxt += vm.selectedSessions[courseId] + ',';
                }
                var length = sessionsIdsTxt.length;
                sessionsIdsTxt = (length > 0) ?
                    sessionsIdsTxt.substring(0, length - 1) :
                    '0';
                
                if (sourceType === 'group') {
                    route = Routing.generate(
                        'api_post_group_register_to_multiple_cursus',
                        {
                            group: sourceId,
                            cursusIdsTxt: cursusIdsTxt,
                            sessionsIdsTxt: sessionsIdsTxt
                        }
                    );
                    $http.post(route).then(function (datas) {
                        
                        if (datas['status'] === 200) {
                            $uibModalStack.dismissAll();
                            $route.reload();
                        }
                    });
                } else if (sourceType === 'user') {
                    route = Routing.generate(
                        'api_post_users_register_to_multiple_cursus',
                        {
                            usersIdsTxt: sourceId,
                            cursusIdsTxt: cursusIdsTxt,
                            sessionsIdsTxt: sessionsIdsTxt
                        }
                    );
                    $http.post(route).then(function (datas) {
                        
                        if (datas['status'] === 200) {
                            $uibModalStack.dismissAll();
                            $route.reload();
                        }
                    });
                }
            };
            
            function initialize()
            {
                var route = Routing.generate(
                    'api_get_sessions_for_cursus_list',
                    {cursusIdsTxt: cursusIdsTxt}
                );
            
                $http.get(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        vm.sessionsDatas = datas['data'];
                        
                        for (var courseId in vm.sessionsDatas) {
                            vm.selectedSessions[courseId] = 0;
                        }
                    }
                });
            }
            
            initialize();
        }
    ]);
})();