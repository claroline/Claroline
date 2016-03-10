angular.module('Exercise').directive('exercisePlayer', [
    function () {
        return {
            restrict: 'E',
            replace: true,
            controller: 'ExercisePlayerCtrl',
            controllerAs: 'exercisePlayerCtrl',
            templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Exercise/Partials/player.html',
            scope: {
                paper: '=',
                exercise: '=',
                user: '=',
                currentStepIndex: '='
            },
            link: function (scope, element, attr, exercisePlayerCtrl) {
                exercisePlayerCtrl.init(scope.paper, scope.exercise, scope.user, scope.currentStepIndex);
            }
        };
    }
]);
