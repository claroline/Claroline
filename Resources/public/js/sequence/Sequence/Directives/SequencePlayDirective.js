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
                    sequence: '=',
                    currentStep: '=',
                    steps: '='
                },
                link: function (scope, element, attr, sequencePlayCtrl) {
                    // set current page to first page
                    console.log('sequence play directive link method called');
                    console.log(scope.sequence);
                    sequencePlayCtrl.setSequence(scope.sequence);
                    sequencePlayCtrl.setSteps(scope.steps);
                    sequencePlayCtrl.setCurrentStep(0);
                    sequencePlayCtrl.setNbAttempts(1);
                    /*scope.$watch('currentStep', function (newValue) {
                        console.log('yep');
                        if (typeof newValue === 'string') {
                            console.log('new');
                            sequencePlayCtrl.currentStep = JSON.parse(newValue);
                        } else {
                            console.log('updated ' );
                            console.log(newValue);
                            sequencePlayCtrl.currentStep = newValue;
                        }
                    });*/
                }
            };
        }
    ]);
})();


