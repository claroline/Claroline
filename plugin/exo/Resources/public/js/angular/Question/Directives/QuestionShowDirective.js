/**
 * Question Show Directive
 * Displays a Question
 * @constructor
 */
var QuestionShowDirective = function QuestionShowDirective() {
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
};

// Set up dependency injection
QuestionShowDirective.$inject = [];

// Register directive into AngularJS
angular
    .module('Question')
    .directive('questionShow', QuestionShowDirective);
