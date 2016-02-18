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

    angular.module('CursusModule').factory('CursusService', [
        '$http',
        function ($http) {
            var initialized = false;
            var cursusDatasReady = false;
            var cursusRoots = null;
            
            return {
                initialize: function () {
                    
                    if (!initialized) {
                        var route = Routing.generate('api_get_all_root_cursus');
                        $http.get(route).then(function (datas) {
                            
                            if (datas['status'] === 200) {
                                cursusRoots = datas['data'];
                                cursusDatasReady = true;
                            }
                        });
                        initialized = true;
                    }
                },
                getCursusRoots: function () {
                    
                    return cursusRoots;
                },
                isCursusDatasReady: function () {
                    
                    return cursusDatasReady;
                }
            };
        }
    ]);
})();