
(function () {
    'use strict';

    angular.module('Question').directive('clozeQuestion', [        
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'ClozeQuestionCtrl',
                controllerAs: 'clozeQuestionCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Question/Partials/cloze.question.html',
                scope: {
                    step: '=',
                    question: '=',
                    selfRemove:"&"
                },
                link: function (scope, element, attr, clozeQuestionCtrl) {
                    console.log('clozeQuestion directive link method called');
                    clozeQuestionCtrl.init(scope.question);
                    clozeQuestionCtrl.setQuestion(scope.question);
                    clozeQuestionCtrl.setQuestionText(scope.question.text);
                }
            };
        }
    ]);
})();


