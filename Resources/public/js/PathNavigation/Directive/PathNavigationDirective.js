(function () {
    'use strict';

    angular.module('PathNavigationModule').directive('pathNavigation', [
        function PathNavigationDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: PathNavigationCtrl,
                controllerAs: 'pathNavigationEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/PathNavigation/Partial/navigation.html',
                scope: {},
                bindToController: true
            };
        }
    ]);
})();