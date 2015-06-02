/**
 * Alert box directive
 */
(function () {
    'use strict';

    angular.module('AlertModule').directive('alertBox', [
        'AlertService',
        function (AlertService) {
            return {
                restrict: 'E',
                replace: true,
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Alert/Partial/alert-box.html',
                scope: {},
                link: function (scope) {
                    scope.current = AlertService.getCurrent();

                    scope.closeCurrent  = function () {
                        AlertService.closeCurrent();
                    };
                }
            }
        }
    ]);
})();