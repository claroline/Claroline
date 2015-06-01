(function () {
    'use strict';

    angular.module('PathNavigationModule').directive('pathNavigation', [
        function PathNavigationDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: PathNavigationCtrl,
                controllerAs: 'pathNavigationCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/PathNavigation/Partial/show.html',
                scope: {},
                bindToController: true
            };
        }
    ]);
})();