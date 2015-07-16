/**
 * Activity Form directive
 * Directive Documentation : https://docs.angularjs.org/guide/directive
 */
(function () {
    'use strict';

    angular.module('Sequence').directive('sequenceEdit', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'SequenceEditCtrl',
                controllerAs: 'sequenceEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Sequence/Partials/sequence.edit.html',
                scope: {
                    sequence: '='
                },
                link: function (scope, element, attr, sequenceEditCtrl) {
                    // set current page to first page
                    console.log('sequence directive link method called');
                    sequenceEditCtrl.setSequence(scope.sequence);                   
                    
                }
            };
        }
    ]);
})();


