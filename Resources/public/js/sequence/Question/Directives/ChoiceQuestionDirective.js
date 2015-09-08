
(function () {
    'use strict';

    angular.module('Question').directive('choiceQuestion', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'ChoiceQuestionCtrl',
                controllerAs: 'choiceQuestionCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Question/Partials/choice.question.html',
                scope: {
                    step: '=',
                    question: '=',
                    selfRemove:"&"
                },
                link: function (scope, element, attr, choiceQuestionCtrl) {
                    console.log('choiceQuestion directive link method called');
                    choiceQuestionCtrl.setQuestion(scope.question);
                }
            };
        }
    ]);
})();


