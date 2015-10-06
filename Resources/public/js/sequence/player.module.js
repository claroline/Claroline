(function () {
    'use strict';
    
    // Resolve functions
    var resolveFunctions = {
        /**
         * Get the current Step from route params
         */
        sequence: [
            '$q',
            '$route',
            'SequenceService',
            function getSequence($q, $route, SequenceService) {
                var defer = $q.defer();

                var sequence = null;

                // Retrieve the exercice from route ID
                if ($route.current.params && $route.current.params.exoId) {
                    sequence = SequenceService.getSeqquence($route.current.params.exoId);
                }

                if (angular.isDefined(sequence) && angular.isObject(sequence)) {
                    defer.resolve(sequence);
                } else {
                    defer.reject('exercice_not_found');
                }

                return defer.promise;
            }
        ]
    };

    // exercise player app
    var exercisePlayer = angular.module('SequencePlayerApp', [
        'ngSanitize',
        'ngRoute',
        'ui.bootstrap',
        'ui.tinymce',
        'ui.translation',
        'ui.resourcePicker',
        'ngBootbox',
        'Common',
        'Sequence'
    ]);
    
    exercisePlayer.filter(
    'unsafe', 
    function($sce) { 
        return $sce.trustAsHtml; 
    });
    
    exercisePlayer.config(['$routeProvider',
        function($routeProvider){
            $routeProvider.
            when('/:exoId', {
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Sequence/Partials/sequence.play.html',
                controller: 'SequencePlayCtrl2',
                resolve: resolveFunctions
            });
        }
    ]);
})();