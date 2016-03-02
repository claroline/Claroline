
(function () {
    'use strict';

    angular.module('Question').directive('choiceQuestion', [
        '$timeout',
        function ($timeout) {
            return {
                restrict: 'E',
                replace: true,
                controller: 'ChoiceQuestionCtrl',
                controllerAs: 'choiceQuestionCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Question/Partials/choice.question.html',
                scope: {
                    question: '=',
                    canSeeFeedback: '='
                },
                link: function (scope, element, attr, choiceQuestionCtrl) {
                    choiceQuestionCtrl.init(scope.question, scope.canSeeFeedback);
                    // in case of coming from a js plumb question
                    $timeout(function(){
                       // in case we come from a match question, 
                       // we need to remove previous connections & endpoints on jsplumb objects
                       // this has to be present on every QuestionDirectives except for MatchQuestionDirective
                       jsPlumb.detachEveryConnection();
                       jsPlumb.deleteEveryEndpoint();
                    });
                    
                }
            };
        }
    ]);
})();


