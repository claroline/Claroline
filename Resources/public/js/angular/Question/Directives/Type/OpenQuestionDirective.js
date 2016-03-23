angular.module('Question').directive('openQuestion', [
    function () {
        return {
            restrict: 'E',
            replace: true,
            controller: 'OpenQuestionCtrl',
            controllerAs: 'openQuestionCtrl',
            templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/open.html',
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


