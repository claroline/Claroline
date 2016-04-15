/**
 * Step Show Directive
 * @constructor
 */
var StepShowDirective = function StepShowDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'StepShowCtrl',
        controllerAs: 'stepShowCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Step/Partials/show.html',
        scope: {
            step      : '=',
            stepIndex: '@'
        }
    };
};

// Set up dependency injection
StepShowDirective.$inject = [];

// Register directive into AngularJS
angular
    .module('Step')
    .directive('stepShow', StepShowDirective);