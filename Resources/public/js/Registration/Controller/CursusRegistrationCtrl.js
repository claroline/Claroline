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

    angular.module('CursusRegistrationModule').controller('CursusRegistrationCtrl', [
        '$http',
        function ($http) {
            var vm = this;
            this.initialized = false;
            this.cursusRoots = [];
            this.hoveredCursusId = 0;
            
            function initialize() {
                
                if (!vm.initialized) {
                    var route = Routing.generate('api_get_all_root_cursus');
                    $http.get(route).then(function (datas) {

                        if (datas['status'] === 200) {
                            vm.cursusRoots = datas['data'];
                            vm.initialized = true;
                        }
                    });
                }
            };
            
            initialize();
        }
    ]);
})();