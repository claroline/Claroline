(function () {
    'use strict';

    angular.module('Step').directive('stepShow', [
        function () {
            return {
                restrict: 'E',
                replace: false,
                controller: 'StepShowCtrl',
                controllerAs: 'stepShowCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Step/Partials/step.show.html',
                scope: {
                    steps: '='
                },
                link: function (scope, element, attr, stepShowCtrl) {
                    console.log('yep');
                    stepShowCtrl.setSteps(scope.steps);
                    
                    stepShowCtrl.setCurrentStep(scope.steps[0]);
                    
                    console.log(scope.steps);
                }
            };
        }
    ]);
})();


