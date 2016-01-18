
(function () {
    'use strict';

    angular.module('Question').directive('clozeQuestion', [  
        '$timeout',      
        function ($timeout) {
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
                clozeQuestionCtrl.setQuestion(scope.question);
                clozeQuestionCtrl.setQuestionText(scope.question.text);
                    $timeout(function(){
                        clozeQuestionCtrl.init(scope.question);
                    });
                }
            };
        }
    ]);
})();


