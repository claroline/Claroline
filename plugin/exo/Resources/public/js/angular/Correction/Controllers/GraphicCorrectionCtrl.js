/**
 * Correction for Graphic Questions
 * @param {QuestionService}        QuestionService
 * @param {GraphicQuestionService} GraphicQuestionService
 * @param {ImageAreaService} ImageAreaService
 * @constructor
 */
var GraphicCorrectionCtrl = function GraphicCorrectionCtrl(QuestionService, GraphicQuestionService, ImageAreaService) {
    AbstractCorrectionCtrl.apply(this, arguments);

    this.GraphicQuestionService = GraphicQuestionService;
    this.ImageAreaService = ImageAreaService;
};

// Extends AbstractQuestionCtrl
GraphicCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
GraphicCorrectionCtrl.$inject = AbstractCorrectionCtrl.$inject.concat([ 'GraphicQuestionService', 'ImageAreaService' ]);

GraphicCorrectionCtrl.prototype.getAreaColor = function getAreaColor(area) {
    return this.ImageAreaService.COLORS[area.color];
}

// Register controller into AngularJS
angular
    .module('Correction')
    .controller('GraphicCorrectionCtrl', GraphicCorrectionCtrl);