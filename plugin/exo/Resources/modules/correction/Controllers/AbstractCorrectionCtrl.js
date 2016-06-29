/**
 * Abstract Correction Controller
 * @param {QuestionService} QuestionService
 * @constructor
 */
function AbstractCorrectionCtrl(QuestionService) {
    this.QuestionService = QuestionService;

    // Create the correct answer from the Question solutions
    this.answer = this.QuestionService.getTypeService(this.question.type).getCorrectAnswer(this.question);
}

/**
 * Current question
 * @type {Object}
 */
AbstractCorrectionCtrl.prototype.question = null;

/**
 * Correct answer for the Question
 * @type {mixed}
 */
AbstractCorrectionCtrl.prototype.answer = null;

export default AbstractCorrectionCtrl
