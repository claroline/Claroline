
(function () {
    'use strict';

    angular.module('Question').directive('matchQuestion', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'MatchQuestionCtrl',
                controllerAs: 'matchQuestionCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Question/Partials/match.question.html',
                scope: {
                    step: '=',
                    question: '=',
                    selfRemove: "&"
                },
                link: function (scope, element, attr, matchQuestionCtrl) {
                    matchQuestionCtrl.setQuestion(scope.question);
                    matchQuestionCtrl.init(scope.question);
                    
                    $("#resetAll").click(function() {
                        jsPlumb.detachEveryConnection();
                    });
                }
            };
        }
    ]);
})();


