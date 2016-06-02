/**
 * Correction for Open Questions
 * @param {QuestionService}     QuestionService
 * @param {OpenQuestionService} OpenQuestionService
 * @constructor
 */
var OpenCorrectionCtrl = function OpenCorrectionCtrl(QuestionService, OpenQuestionService) {
    AbstractCorrectionCtrl.apply(this, arguments);

    this.OpenQuestionService = OpenQuestionService;
};

// Extends AbstractQuestionCtrl
OpenCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
OpenCorrectionCtrl.$inject = AbstractCorrectionCtrl.$inject.concat([ 'OpenQuestionService' ]);

OpenCorrectionCtrl.prototype.getKeywordStats = function getKeywordStats(keyword) {
    var stats = null;

    if (this.question.solutions) {
        for (var i = 0; i < this.question.solutions; i++) {
            if (this.question.solutions[i].id == keyword.id) {
                stats = this.question.solutions[i];
            }
        }

        if (!stats) {
            // No User have chosen this answer
            stats = {
                id: keyword.id,
                count: 0
            };
        }
    }

    return stats;
};

// Register controller into AngularJS
angular
    .module('Correction')
    .controller('OpenCorrectionCtrl', OpenCorrectionCtrl);
