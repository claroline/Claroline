import AbstractQuestionDirective from './AbstractQuestionDirective'
import open from './../../Partials/Type/open.html'

/**
 * Open Question Directive
 * Manages Question of types Open
 *
 * @returns {Object}
 * @constructor
 */
function OpenQuestionDirective() {
    return angular.merge({}, AbstractQuestionDirective.apply(this, arguments), {
        controller: 'OpenQuestionCtrl',
        controllerAs: 'openQuestionCtrl',
        template: open
    });
}

// Extends AbstractQuestionDirective
OpenQuestionDirective.prototype = Object.create(AbstractQuestionDirective.prototype);

// Set up dependency injection (get DI from parent too)
OpenQuestionDirective.$inject = AbstractQuestionDirective.$inject;

export default OpenQuestionDirective
