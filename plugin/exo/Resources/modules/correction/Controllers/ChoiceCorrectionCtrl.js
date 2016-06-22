import AbstractCorrectionCtrl from './AbstractCorrectionCtrl'

/**
 * Correction for Choice question
 * @param {QuestionService}       QuestionService
 * @param {ChoiceQuestionService} ChoiceQuestionService
 * @constructor
 */
function ChoiceCorrectionCtrl(QuestionService, ChoiceQuestionService) {
    AbstractCorrectionCtrl.apply(this, arguments);

    this.ChoiceQuestionService = ChoiceQuestionService;
};

// Extends AbstractQuestionCtrl
ChoiceCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype);

/**
 * Check if a choice has been selected by User
 * @param   {Object} choice
 * @returns {Boolean}
 */
ChoiceCorrectionCtrl.prototype.isChoiceSelected = function isChoiceSelected(choice) {
    return this.ChoiceQuestionService.isChoiceSelected(this.answer, choice);
};

ChoiceCorrectionCtrl.prototype.getChoiceSolution = function getChoiceScore(choice) {
    return this.ChoiceQuestionService.getChoiceSolution(this.question, choice);
};

ChoiceCorrectionCtrl.prototype.getChoiceStats = function getChoiceStats(choice) {
    return this.ChoiceQuestionService.getChoiceStats(this.question, choice);
};

export default ChoiceCorrectionCtrl
