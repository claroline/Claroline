(function () {
    'use strict';

    angular.module('StepConditionsModule').directive('stepConditionsEdit', [
        function StepConditionsEditDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: StepConditionsEditCtrl,
                controllerAs: 'stepConditionsEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/StepConditions/Partial/edit.html',
                scope: {
                    next: '='
                },
                bindToController: true
            };
        }
    ]);
})();