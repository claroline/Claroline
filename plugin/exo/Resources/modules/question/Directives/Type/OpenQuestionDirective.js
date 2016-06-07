/**
 * Open Question Directive
 * Manages Question of types Open
 *
 * @returns {Object}
 * @constructor
 */
var OpenQuestionDirective = function OpenQuestionDirective() {
    return angular.merge({}, AbstractQuestionDirective.apply(this, arguments), {
        controller: 'OpenQuestionCtrl',
        controllerAs: 'openQuestionCtrl',
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/Partials/Type/open.html'
    });
};

// Extends AbstractQuestionDirective
OpenQuestionDirective.prototype = Object.create(AbstractQuestionDirective.prototype);

// Set up dependency injection (get DI from parent too)
OpenQuestionDirective.$inject = AbstractQuestionDirective.$inject;

// Register directive into AngularJS
angular
    .module('Question')
    .directive('openQuestion', OpenQuestionDirective);
