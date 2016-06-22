import AbstractCorrectionCtrl from './AbstractCorrectionCtrl'

/**
 * Correction for Graphic Questions
 * @param {QuestionService}        QuestionService
 * @param {GraphicQuestionService} GraphicQuestionService
 * @param {ImageAreaService} ImageAreaService
 * @constructor
 */
function GraphicCorrectionCtrl(QuestionService, GraphicQuestionService, ImageAreaService) {
    AbstractCorrectionCtrl.apply(this, arguments);

    this.GraphicQuestionService = GraphicQuestionService;
    this.ImageAreaService = ImageAreaService;
}

// Extends AbstractQuestionCtrl
GraphicCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype);

GraphicCorrectionCtrl.prototype.getAreaColor = function getAreaColor(area) {
    return this.ImageAreaService.COLORS[area.color];
};

GraphicCorrectionCtrl.prototype.getAreaStats = function getAreaStats(areaId) {
    return this.GraphicQuestionService.getAreaStats(this.question, areaId);
};

export default GraphicCorrectionCtrl
