
(function () {
    'use strict';

    angular.module('Question').directive('matchQuestion', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'MatchQuestionCtrl',
                controllerAs: 'matchQuestionCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Question/Partials/match.question.html',
                scope: {
                    step: '=',
                    question: '=',
                    selfRemove:"&"
                },
                link: function (scope, element, attr, matchQuestionCtrl) {
                    console.log('matchQuestion directive link method called');
                    matchQuestionCtrl.setQuestion(scope.question);
                }
            };
        }
    ]);
})();


