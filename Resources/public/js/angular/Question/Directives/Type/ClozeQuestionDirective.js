angular.module('Question').directive('clozeQuestion', [
    '$timeout',
    function ($timeout) {
        return {
            restrict: 'E',
            replace: true,
            controller: 'ClozeQuestionCtrl',
            controllerAs: 'clozeQuestionCtrl',
            templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/cloze.html',
            scope: {
                step: '=',
                question: '=',
                selfRemove:"&"
            },
            link: function (scope, element, attr, clozeQuestionCtrl) {
            clozeQuestionCtrl.setQuestion(scope.question);
            clozeQuestionCtrl.setQuestionText(scope.question.text);
                $timeout(function(){
                    jsPlumb.detachEveryConnection();
                    jsPlumb.deleteEveryEndpoint();
                    clozeQuestionCtrl.init(scope.question);
                });
            }
        };
    }
]);
