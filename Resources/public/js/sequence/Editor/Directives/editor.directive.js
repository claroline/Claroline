/**
 * Editor Directive
 * Renders the Editor for an Exercise
 */
(function () {
    'use strict';

    angular
        .module('ExerciseEditorApp')
        .directive('exercisePlayer', [
            function () {
                return {
                    restrict: 'E',
                    replace: true,
                    controller: 'ExercisePlayerCtrl',
                    controllerAs: 'exercisePlayerCtrl',
                    templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Exercise/Partials/player.directive.html',
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
})();




