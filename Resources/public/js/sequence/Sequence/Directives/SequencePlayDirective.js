(function () {
    'use strict';

    angular.module('Sequence').directive('sequencePlay', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'SequencePlayCtrl',
                controllerAs: 'sequencePlayCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Sequence/Partials/sequence.play.html',
                scope: {
                    sequence: '='
                },
                link: function (scope, element, attr, sequencePlayCtrl) {
                    // set current page to first page
                    console.log('sequence play directive link method called');
                    sequencePlayCtrl.setSequence(scope.sequence);
                    //sequencePlayCtrl.setSteps(scope.sequence.steps);
                    sequencePlayCtrl.setCurrentStep(0);
                    sequencePlayCtrl.setNbAttempts(1);
                }
            };
        }
    ]);
})();


