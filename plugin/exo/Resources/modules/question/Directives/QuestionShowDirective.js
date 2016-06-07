/**
 * Question Show Directive
 * Displays a Question
 * @constructor
 */
function QuestionShowDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'QuestionShowCtrl',
        controllerAs: 'questionShowCtrl',
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/show.html',
        scope: {
            question: '=',
            questionPaper: '=',
            includeCorrection: '=', // Is the solution for the current question displayed ?
            collapsed: '=?'
        },
        bindToController: true
    };
}
