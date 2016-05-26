/**
 * Choice Question Directive
 * Manages Question of types Choice
 *
 * @returns {object}
 * @constructor
 */
var ChoiceQuestionDirective = function ChoiceQuestionDirective() {
    return angular.merge({}, AbstractQuestionDirective.apply(this, arguments), {
        controller: 'ChoiceQuestionCtrl',
        controllerAs: 'choiceQuestionCtrl',
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/choice.html'
    });
};

// Extends AbstractQuestionDirective
ChoiceQuestionDirective.prototype = Object.create(AbstractQuestionDirective.prototype);

// Set up dependency injection (get DI from parent too)
ChoiceQuestionDirective.$inject = AbstractQuestionDirective.$inject;

// Register directive into AngularJS
angular
    .module('Question')
    .directive('choiceQuestion', ChoiceQuestionDirective);
