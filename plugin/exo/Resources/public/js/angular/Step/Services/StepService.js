/**
 * Step Service
 * @param {QuestionService} QuestionService
 * @constructor
 */
var StepService = function StepService(QuestionService) {
    this.QuestionService = QuestionService;
};

// Set up dependency injection
StepService.$inject = [ 'QuestionService' ];

/**
 * Get a Step question by its ID
 * @param   {Object} step
 * @param   {String} questionId
 * @returns {Object|null}
 */
StepService.prototype.getQuestion = function getQuestion(step, questionId) {
    var question = null;

    if (step && step.items && 0 !== step.items.length) {
        question = this.QuestionService.getQuestion(step.items, questionId);
    }

    return question;
};

// Register service into AngularJS
angular
    .module('Step')
    .service('StepService', StepService);