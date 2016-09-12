import show from './../Partials/show.html'

/**
 * Question Show Directive
 * Displays a Question
 * @constructor
 */
export default function QuestionShowDirective() {
  return {
    restrict: 'E',
    replace: true,
    controller: 'QuestionShowCtrl',
    controllerAs: 'questionShowCtrl',
    template: show,
    scope: {
      question: '=',
      questionPaper: '=',
      includeCorrection: '=', // Is the solution for the current question displayed ?
      minimalCorrection: '=', // shall we display expected answer field in correction ?
      showScore: '=', // shall we display score info ?
      collapsed: '=?'
    },
    bindToController: true
  }
}
