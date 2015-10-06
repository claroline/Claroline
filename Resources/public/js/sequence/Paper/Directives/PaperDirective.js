
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
                    context: '@'
                },
                link: function (scope, element, attr, paperCtrl) {
                    console.log('paperDetails directive link method called');
                    paperCtrl.init(scope.paper, scope.context);
                    /*var context = scope.context;
                    scope.$watch('paper', function (newValue) {
                        if (typeof newValue === 'string') {
                            paperCtrl.init(newValue, context);
                        } else {
                            paperCtrl.init(newValue, context);
                        }
                    });*/
                }
            };
        }
    ]);
})();


