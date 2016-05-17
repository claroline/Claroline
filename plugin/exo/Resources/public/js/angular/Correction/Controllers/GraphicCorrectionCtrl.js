/**
 * Correction for Graphic Questions
 * @param {QuestionService}        QuestionService
 * @param {GraphicQuestionService} GraphicQuestionService
 * @constructor
 */
var GraphicCorrectionCtrl = function GraphicCorrectionCtrl(QuestionService, GraphicQuestionService) {
    AbstractCorrectionCtrl.apply(this, arguments);

    this.GraphicQuestionService = GraphicQuestionService;
};

// Extends AbstractQuestionCtrl
GraphicCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
GraphicCorrectionCtrl.$inject = AbstractCorrectionCtrl.$inject.concat([ 'GraphicQuestionService' ]);

/**
 * Get the full URL of the Image
 * @returns {string}
 */
GraphicCorrectionCtrl.prototype.getImageUrl = function getImageUrl() {
    return this.GraphicQuestionService.getImageUrl(this.question);
};

GraphicCorrectionCtrl.prototype.createElements = function createElements() {
    // create correction elements (backgrounds)
    for (var i = 0; i < this.question.solutions.length; i++) {
        var solution = this.question.solutions[i];
        var elem = document.createElement('div');
        var style = '';
        style += 'position:absolute;';
        style += 'border:1px solid #eee;';
        style += 'opacity:0.6;';
        style += 'height:' + solution.size.toString() + 'px;';
        style += 'width:' + solution.size.toString() + 'px;';

        if (solution.shape === "circle") {
            style += 'border-radius:50%;';
        }
        var coords = solution.value.split(',');
        var imgPosition = $('#document-img-' + this.question.id).position();
        var topValue = imgPosition.top + parseFloat(coords[1]);
        var leftValue = imgPosition.left + parseFloat(coords[0]);

        style += 'top:' + topValue.toString() + 'px;';
        style += 'left:' + leftValue.toString() + 'px;';
        style += 'background-color:' + solution.color + ';';
        elem.setAttribute('style', style);
        document.getElementById('document-img-container-' + this.question.id).appendChild(elem);
    }
};

// Register controller into AngularJS
angular
    .module('Correction')
    .controller('GraphicCorrectionCtrl', GraphicCorrectionCtrl);