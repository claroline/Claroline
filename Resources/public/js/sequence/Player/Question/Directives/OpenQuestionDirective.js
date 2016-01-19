
(function () {
    'use strict';

    angular.module('Question').directive('openQuestion', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'OpenQuestionCtrl',
                controllerAs: 'openQuestionCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Question/Partials/open.question.html',
                scope: {
                    question: '=',
                    canSeeFeedback: '='
                },
                link: function (scope, element, attr, openQuestionCtrl) {
                    jsPlumb.detachEveryConnection();
                    jsPlumb.deleteEveryEndpoint();
                    openQuestionCtrl.init(scope.question, scope.canSeeFeedback);                 
                    
                }
            };
        }
    ]);
})();


