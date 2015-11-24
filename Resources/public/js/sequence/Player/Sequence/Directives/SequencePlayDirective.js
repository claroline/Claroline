(function () {
    'use strict';

    angular.module('Sequence').directive('sequencePlay', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'SequencePlayCtrl',
                controllerAs: 'sequencePlayCtrl',                
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Sequence/Partials/sequence.play.html',
                scope: {
                    sequence: '=',
                    paper: '=',
                    user:'@'
                },
                link: function (scope, element, attr, sequencePlayCtrl) {
                    sequencePlayCtrl.init(scope.sequence, scope.paper, scope.user);                    
                }
            };
        }
    ]);
})();


