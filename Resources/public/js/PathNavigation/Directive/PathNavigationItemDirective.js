(function () {
    'use strict';

    angular.module('PathNavigationModule').directive('pathNavigationItem', [
        function PathNavigationDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: [
                    'AuthorizationCheckerService',
                    'PathService',
                    function PathNavigationItemCtrl(AuthorizationCheckerService, PathService) {
                        this.goTo = function goTo() {

                        }
                    }
                ],
                controllerAs: 'pathNavigationItemCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/PathNavigation/Partial/item.html',
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