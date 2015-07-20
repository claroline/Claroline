(function () {
    'use strict';

    angular.module('Step').directive('stepEdit', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'StepEditCtrl',
                controllerAs: 'stepEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Step/Partials/step.edit.html',
                scope: {
                    steps: '='
                },
                link: function (scope, element, attr, stepEditCtrl) {
                    // set current page to first page
                    console.log('step edit directive link method called');
                    stepEditCtrl.setSteps(scope.steps);
                }
            };
        }
    ]);
})();
