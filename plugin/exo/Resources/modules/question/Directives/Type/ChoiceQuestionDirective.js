import AbstractQuestionDirective from './AbstractQuestionDirective'
import choice from './../../Partials/Type/choice.html'

/**
 * Choice Question Directive
 * Manages Question of types Choice
 *
 * @returns {object}
 * @constructor
 */
function ChoiceQuestionDirective() {
    return angular.merge({}, AbstractQuestionDirective.apply(this, arguments), {
        controller: 'ChoiceQuestionCtrl',
        controllerAs: 'choiceQuestionCtrl',
        template: choice
    });
}

// Set up dependency injection (get DI from parent too)
ChoiceQuestionDirective.$inject = AbstractQuestionDirective.$inject;

export default ChoiceQuestionDirective
