/**
 * ExercisePlayerApp
 */

(function () {
    'use strict';

    var dependencies = [
        'ngSanitize',
        'ngRoute',
        'angular-loading-bar',
        'ui.bootstrap',
        'ui.translation',
        'ngBootbox',
        'Common',
        'PlayerSharedServices',
        'Question'
    ];
    // exercise player module
    var ExercisePlayerApp = angular.module('ExercisePlayerApp', dependencies);
    
   
    
    var resolvePlayerData = {
        /**
         * Get the paper details
         */
        data: [
            '$route',
            'ExerciseService',
            function getExercise($route, ExerciseService) {

                var promise = null;
                if ($route.current.params && $route.current.params.eid) {
                    promise = ExerciseService.getExercise($route.current.params.eid);

                }
                return promise;
            }
        ],
        user: [
            '$route',
            'ExerciseService',
            function getConnectedUserInfos($route, ExerciseService) {

                var promise = null;
                if ($route.current.params && $route.current.params.eid) {
                    promise = ExerciseService.getConnectedUser($route.current.params.eid);
                }
                return promise;
            }
        ]
    };
    
    
    
    
    ExercisePlayerApp.config([
        '$routeProvider',
        'cfpLoadingBarProvider',
        function ExercisePlayerAppConfig($routeProvider, cfpLoadingBarProvider) {
            $routeProvider
                    .when('/:eid/:sid?', {
                        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Exercise/Partials/exercise.step.html',
                        controller: 'ExerciseCtrl',
                        controllerAs: 'exerciseCtrl',
                        resolve: resolvePlayerData
                    })
                    .otherwise({
                        redirectTo: '/:eid'
                    });
            // please wait spinner config
            cfpLoadingBarProvider.latencyThreshold = 200;
            cfpLoadingBarProvider.includeBar = false;
            cfpLoadingBarProvider.spinnerTemplate = '<div class="loading">Loading&#8230;</div>';
        }
    ]);
    
     ExercisePlayerApp.filter(
            'unsafe',
            function ($sce) {
                return $sce.trustAsHtml;
            });
})();

