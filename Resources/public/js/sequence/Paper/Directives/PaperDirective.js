
(function () {
    'use strict';

    angular.module('Paper').directive('paperDetails', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PaperCtrl',
                controllerAs: 'paperCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Paper/Partials/paper.details.html',
                scope: {
                    paper: '=',
                    context: '@',
                    exoId: '@'
                },
                link: function (scope, element, attr, paperCtrl) {
                    paperCtrl.init(scope.paper, scope.context, scope.exoId);
                }
            };
        }
    ]);
})();


