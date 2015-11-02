(function () {
    'use strict';

    angular.module('PathNavigationModule').directive('pathNavigationShow', [
        function PathNavigationShowDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: PathNavigationShowCtrl,
                controllerAs: 'pathNavigationShowCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/PathNavigation/Partial/show.html',
                scope: {},
                bindToController: true
            };
        }
    ]);
})();