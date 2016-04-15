/**
 * Step Service
 * @constructor
 */
var StepService = function StepService() {

};

// Set up dependency injection
StepService.$inject = [];

// Register service into AngularJS
angular
    .module('Step')
    .service('StepService', StepService);