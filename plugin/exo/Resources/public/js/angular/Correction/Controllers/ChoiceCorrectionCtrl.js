/**
 * Correction for Choice question
 * @param {QuestionService}       QuestionService
 * @param {ChoiceQuestionService} ChoiceQuestionService
 * @constructor
 */
var ChoiceCorrectionCtrl = function (QuestionService, ChoiceQuestionService) {
    AbstractCorrectionCtrl.apply(this, arguments);

    this.ChoiceQuestionService = ChoiceQuestionService;
};

// Extends AbstractQuestionCtrl
ChoiceCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
ChoiceCorrectionCtrl.$inject = AbstractCorrectionCtrl.$inject.concat([ 'ChoiceQuestionService' ]);

/**
 * Check if a choice has been selected by User
 * @param   {Object} choice
 * @returns {Boolean}
 */
ChoiceCorrectionCtrl.prototype.isChoiceSelected = function isChoiceSelected(choice) {
    return this.ChoiceQuestionService.isChoiceSelected(this.answer, choice);
};

ChoiceCorrectionCtrl.prototype.getChoiceFeedback = function getChoiceFeedback(choice) {
    return this.ChoiceQuestionService.getChoiceFeedback(this.question, choice);
};

// Register controller into AngularJS
angular
    .module('Correction')
    .controller('ChoiceCorrectionCtrl', ChoiceCorrectionCtrl);
