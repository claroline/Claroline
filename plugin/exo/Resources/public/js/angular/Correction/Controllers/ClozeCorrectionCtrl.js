/**
 * Correction for Cloze question
 * @param {QuestionService}      QuestionService
 * @param {ClozeQuestionService} ClozeQuestionService
 * @constructor
 */
var ClozeCorrectionCtrl = function (QuestionService, ClozeQuestionService) {
    AbstractCorrectionCtrl.apply(this, arguments);

    this.ClozeQuestionService = ClozeQuestionService;
};

// Extends AbstractQuestionCtrl
ClozeCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
ClozeCorrectionCtrl.$inject = AbstractCorrectionCtrl.$inject.concat([ 'ClozeQuestionService' ]);

/**
 * Get the answer for a Hole
 * @param   {Object} hole
 * @returns {Object}
 */
ClozeCorrectionCtrl.prototype.getHoleAnswer = function getHoleAnswer(hole) {
    return this.ClozeQuestionService.getHoleAnswer(this.answer, hole);
};

ClozeCorrectionCtrl.prototype.getHoleFeedback = function getHoleFeedback(hole) {
    return this.ClozeQuestionService.getHoleFeedback(this.question, hole);
};

// Register controller into AngularJS
angular
    .module('Correction')
    .controller('ClozeCorrectionCtrl', ClozeCorrectionCtrl);