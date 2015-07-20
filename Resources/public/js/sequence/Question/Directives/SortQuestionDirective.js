
(function () {
    'use strict';

    angular.module('Question').directive('sortQuestion', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'SortQuestionCtrl',
                controllerAs: 'sortQuestionCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Question/Partials/sort.question.html',
                scope: {
                    step: '=',
                    question: '='
                },
                link: function (scope, element, attr, sortQuestionCtrl) {
                    console.log('sortQuestion directive link method called');
                }
            };
        }
    ]);
})();


