(function () {
    'use strict';

    angular.module('PathNavigationModule').directive('pathNavigationItem', [
        function PathNavigationDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: function PathNavigationItemCtrl() {},
                controllerAs: 'pathNavigationItemCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/PathNavigation/Partial/navigation-item.html',
                scope: {
                    parent:  '=?',
                    element: '=',
                    current: '='
                },
                bindToController: true
            };
        }
    ]);
})();