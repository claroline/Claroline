/**
 * Open Question Directive
 * Manages Question of types Open
 *
 * @returns {object}
 * @constructor
 */
var OpenQuestionDirective = function OpenQuestionDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'OpenQuestionCtrl',
        controllerAs: 'openQuestionCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/open.html',
        scope: {
            question: '=',
            questionPaper: '='
        }
    };
};


// Set up dependency injection
OpenQuestionDirective.$inject = [];

// Register directive into AngularJS
angular
    .module('Question')
    .directive('openQuestion', OpenQuestionDirective);
