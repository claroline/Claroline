import exercise from './../Partials/exercise.html'

/**
 * Exercise Directive
 * Displays the summary of the Exercise and the links to the available actions for current User
 * @constructor
 */
export default function ExerciseDirective() {
  return {
    restrict: 'E',
    replace: true,
    controller: 'ExerciseCtrl',
    controllerAs: 'exerciseCtrl',
    template: exercise,
    scope: {
      exercise     : '=', // The current Exercise to display
      nbPapers     : '@', // The number of Papers submitted for this Exercise
      nbUserPapers : '@', // The number of Papers submitted by the current User for this Exercise
      editEnabled  : '=', // User is allowed to edit current exercise ?
      offline      : '='
    },
    bindToController: true
  }
}
