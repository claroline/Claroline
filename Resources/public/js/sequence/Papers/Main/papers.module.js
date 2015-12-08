/**
 * This is a container app used to handle routes for paper views
 * Paper list
 * Paper details
 *  ->  This one uses paper directive to display paper details. 
 *      This directive has been created to handle the case when a correction has to be shown immediatly after a question answer
 */
(function () {
    'use strict';

    var dependencies = [
        'ngSanitize',
        'angular-loading-bar',
        'ngRoute',
        'ui.bootstrap',
        'ui.tinymce',
        'ui.translation',
        'ui.resourcePicker',
        'ngBootbox',
        'angular-table',
        'Common',
        'Correction'
    ];

    // exercise papers module
    var papersApp = angular.module('PapersApp', dependencies);

    var resolvePaperDetailsData = {
        /**
         * Get the paper details
         */
        paperPromise: [
            '$route',
            'CorrectionService',
            function getPaper($route, CorrectionService) {

                var promise = null;
                if ($route.current.params && $route.current.params.eid && $route.current.params.pid) {
                    promise = CorrectionService.getOne($route.current.params.eid, $route.current.params.pid);
                }
                return promise;
            }
        ],
        paperExercise: [
            '$route',
            'PapersService',
            function getSequence($route, PapersService) {

                var promise = null;
                if ($route.current.params && $route.current.params.eid) {
                    promise = PapersService.getSequence($route.current.params.eid);
                }
                return promise;
            }
        ],
        user: [
            '$route',
            'PapersService',
            function getConnectedUserInfos($route, PapersService) {

                var promise = null;
                if ($route.current.params && $route.current.params.eid) {
                    promise = PapersService.getConnectedUser($route.current.params.eid);
                }
                return promise;
            }
        ]
    };

    var resolvePaperListData = {
        paperExercise: [
            '$route',
            'PapersService',
            function getSequence($route, PapersService) {

                var promise = null;
                if ($route.current.params && $route.current.params.eid) {
                    promise = PapersService.getSequence($route.current.params.eid);

                }
                return promise;
            }
        ],
        user: [
            '$route',
            'PapersService',
            function getConnectedUserInfos($route, PapersService) {

                var promise = null;
                if ($route.current.params && $route.current.params.eid) {
                    promise = PapersService.getConnectedUser($route.current.params.eid);
                }
                return promise;
            }
        ]
    };


    papersApp.config([
        '$routeProvider',
        '$locationProvider',
        'cfpLoadingBarProvider',
        function PapersModuleConfig($routeProvider, $locationProvider, cfpLoadingBarProvider) {
            $routeProvider
                    .when('/:eid', {
                        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Papers/Partials/papers.list.html',
                        controller: 'PaperListCtrl',
                        controllerAs: 'paperListCtrl',
                        resolve: resolvePaperListData
                    })
                    .when('/:eid/:pid', {
                        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Papers/Partials/paper.show.html',
                        controller: 'PaperDetailsCtrl',
                        controllerAs: 'paperDetailsCtrl',
                        resolve: resolvePaperDetailsData
                    })
                    .otherwise({
                        redirectTo: '/:eid'
                    });
            //$locationProvider.html5Mode({enabled:true, requireBase:false});
            // please wait spinner config
            cfpLoadingBarProvider.latencyThreshold = 200;
            cfpLoadingBarProvider.includeBar = false;
            cfpLoadingBarProvider.spinnerTemplate = '<div class="loading">Loading&#8230;</div>';
        }
    ]);


    papersApp.filter(
            'unsafe',
            function ($sce) {
                return $sce.trustAsHtml;
            });
    papersApp.filter(
            'mySqlDateToLocalDate',
            function () {
                return function (date) {
                    var searched = new RegExp('-', 'g');
                    var localDate = new Date(Date.parse(date.replace(searched, '/')));
                    return localDate.toLocaleString();
                };
            });
})();