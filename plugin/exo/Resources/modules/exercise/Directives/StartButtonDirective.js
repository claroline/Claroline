/**
 * Start (or restart) the Exercise button
 * @constructor
 */
function StartButtonDirective() {
    return {
        restrict: 'E',
        replace: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Exercise/Partials/start-button.html',
        scope: {
            paperLink: '=',
            overviewLink: '='
        },
        bindToController: true,
        controllerAs: 'startBtnCtrl',
        controller: [
            'ExerciseService',
            'UserPaperService',
            function StartButtonCtrl(ExerciseService, UserPaperService) {
                this.exercise = ExerciseService.getExercise();
                this.nbUserPapers = UserPaperService.getNbPapers();

                /**
                 * Check if the current User can play the exercise
                 * @return boolean
                 */
                this.isComposeEnabled = function isComposeEnabled() {
                    return ExerciseService.isEditEnabled || UserPaperService.isAllowedToCompose();
                };
            }
        ]
    };
}
