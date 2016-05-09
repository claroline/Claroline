/**
 * Step Service
 * @constructor
 */
var StepService = function StepService() {

};

// Set up dependency injection
StepService.$inject = [];

/**
 * Get a Step question by its ID
 * @param   {Object} step
 * @param   {String} questionId
 * @returns {Object|null}
 */
StepService.prototype.getQuestion = function getQuestion(step, questionId) {
    var question = null;

    if (step && step.items && 0 !== step.items) {
        for (var i = 0; i < step.items.length; i++) {
            if (questionId === step.items[i].id) {
                // Question found
                question = step.items[i];
                break; // Stop searching
            }
        }
    }

    return question;
};

// Register service into AngularJS
angular
    .module('Step')
    .service('StepService', StepService);