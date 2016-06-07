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
            collapsed: '=?'
        },
        bindToController: true
    };
}
