(function () {
    'use strict';

    angular.module('Sequence').directive('sequencePlay', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'SequencePlayCtrl',
                controllerAs: 'sequencePlayCtrl',                
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Sequence/Partials/sequence.play.html',
                scope: {
                    sequence: '=',
                    paper: '=',
                    attempts: '@',
                    user:'@'
                },
                link: function (scope, element, attr, sequencePlayCtrl) {
                    sequencePlayCtrl.init(scope.sequence, scope.paper, scope.attempts, scope.user);                    
                }
            };
        }
    ]);
})();


