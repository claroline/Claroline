/**
 * Manages Path form
 */
(function () {
    'use strict';

    angular.module('PathModule').directive('pathForm', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PathFormCtrl',
                controllerAs: 'pathFormCtrl',
                templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Path/Partial/form.html',
                scope: {
                    path: '='
                },
                link: function (scope, element, attrs, pathFormCtrl) {
                    scope.$watch('path', function (newValue) {
                        console.log('path modified');

                        if (typeof newValue === 'string') {
                            pathFormCtrl.path = JSON.parse(newValue);
                        } else {
                            pathFormCtrl.path = newValue;
                        }
                    });
                }
            };
        }
    ]);
})();