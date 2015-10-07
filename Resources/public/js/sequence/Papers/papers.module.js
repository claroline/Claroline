/**
 * This is a container app used to handle routes for paper views
 * 
 */
(function () {
    'use strict';

    // exercise papers module
    var papersApp = angular.module('PapersApp', [
        'ngSanitize',
        'ngRoute',
        'ui.bootstrap',
        'ui.tinymce',
        'ui.translation',
        'ui.resourcePicker',
        'ngBootbox',
        'Common',
        'Paper'
    ]);


    var resolvePaperDetailsData = {
        /**
         * Get the Root step of the Path
         */
        paperPromise: [
            '$route',
            'PapersService',
            function getPaper($route, PapersService) {                             

                var promise = null;
                if ($route.current.params && $route.current.params.pid) {
                    promise = PapersService.getOne($route.current.params.pid);
                    
                }
                return promise;

            }
        ]
    };


    papersApp.config([
        '$routeProvider',
        '$locationProvider',
        function PapersConfig($routeProvider, $locationProvider) {
            $routeProvider
                    .when('/:eid', {
                        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Papers/Partials/papers.list.html',
                        controller: 'PaperListCtrl',
                        controllerAs: 'paperListCtrl'
                    })
                    .when('/:eid/:pid', {
                        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Papers/Partials/paper.show.html',
                        controller: 'PaperDetailsCtrl',
                        controllerAs: 'paperDetailsCtrl',
                        resolve: resolvePaperDetailsData
                    })
                    .otherwise({
                        redirectTo: '/'
                    });
            //$locationProvider.html5Mode({enabled:true, requireBase:false});
        }
    ]);
    papersApp.filter(
            'unsafe',
            function ($sce) {
                return $sce.trustAsHtml;
            });
})();