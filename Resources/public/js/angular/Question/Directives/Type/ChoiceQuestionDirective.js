/**
 * Choice Question Directive
 * Manages Question of types Choice
 *
 * @returns {object}
 * @constructor
 */
var ChoiceQuestionDirective = function ChoiceQuestionDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'ChoiceQuestionCtrl',
        controllerAs: 'choiceQuestionCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/choice.html',
        scope: {
            question     : '=',
            questionPaper: '='
        }
    };
};

// Set up dependency injection
ChoiceQuestionDirective.$inject = [];

// Register directive into AngularJS
angular
    .module('Question')
    .directive('choiceQuestion', ChoiceQuestionDirective);
