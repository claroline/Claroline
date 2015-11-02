(function () {
    'use strict';

    angular.module('PathNavigationModule').directive('pathNavigationEdit', [
        function PathNavigationEditDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: PathNavigationEditCtrl,
                controllerAs: 'pathNavigationEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/PathNavigation/Partial/edit.html',
                scope: {},
                bindToController: true
            };
        }
    ]);
})();