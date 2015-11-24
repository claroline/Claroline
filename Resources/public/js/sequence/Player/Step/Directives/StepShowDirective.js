(function () {
    'use strict';

    angular.module('Step').directive('stepShow', [
        function () {
            return {
                restrict: 'E',
                replace: false,
                controller: 'StepShowCtrl',
                controllerAs: 'stepShowCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Step/Partials/step.show.html',
                scope: {
                    currentStep: '='
                },
                link: function (scope, element, attr, stepShowCtrl) {
                    
                    scope.$watch('currentStep', function (newValue) {
                        if (typeof newValue === 'string') {
                            stepShowCtrl.currentStep = JSON.parse(newValue);
                        } else {
                            stepShowCtrl.currentStep = newValue;
                        }
                    });
                }
            };
        }
    ]);
})();


