import './AbstractQuestionDirective'

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
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/choice.html'
    });
}

// Set up dependency injection (get DI from parent too)
ChoiceQuestionDirective.$inject = AbstractQuestionDirective.$inject;
