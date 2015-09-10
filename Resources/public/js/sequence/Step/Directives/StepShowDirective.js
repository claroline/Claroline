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
                    step: '='
                },
                link: function (scope, element, attr, stepShowCtrl) {
                    console.log('step show directive link method called');            
                    stepShowCtrl.setCurrentStep(scope.step);
                }
            };
        }
    ]);
})();


