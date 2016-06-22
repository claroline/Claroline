import startButton from './../Partials/start-button.html'

/**
 * Start (or restart) the Exercise button
 * @constructor
 */
export default function StartButtonDirective() {
    return {
        restrict: 'E',
        replace: true,
        template: startButton,
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
