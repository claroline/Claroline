angular.module('Question').directive('graphicQuestion', [
    '$timeout',
    function ($timeout) {
        return {
            restrict: 'E',
            replace: true,
            controller: 'GraphicQuestionCtrl',
            controllerAs: 'graphicQuestionCtrl',
            templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/graphic.html',
            scope: {
                question: '=',
                questionPaper: '='
            },
            bindToController: true,
            link: function (scope, element, attr, graphicQuestionCtrl) {
                graphicQuestionCtrl.init(scope.question, scope.canSeeFeedback);
                // in case of coming from a js plumb question
                $timeout(function(){
                   // in case we come from a match question,
                   // we need to remove previous connections & endpoints on jsplumb objects
                   // this has to be present on every QuestionDirectives except for MatchQuestionDirective
                   jsPlumb.detachEveryConnection();
                   jsPlumb.deleteEveryEndpoint();

                   graphicQuestionCtrl.initPreviousAnswers();
                   graphicQuestionCtrl.initDragAndDrop();
                });

            }
        };
    }
]);
