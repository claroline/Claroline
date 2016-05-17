/**
 * Correction for Open Questions
 * @param {QuestionService}     QuestionService
 * @param {OpenQuestionService} OpenQuestionService
 * @constructor
 */
var OpenCorrectionCtrl = function OpenCorrectionCtrl(QuestionService, OpenQuestionService) {
    AbstractCorrectionCtrl.apply(this, arguments);

    this.OpenQuestionService = OpenQuestionService;

    console.log(this.answer);
};

// Extends AbstractQuestionCtrl
OpenCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
OpenCorrectionCtrl.$inject = AbstractCorrectionCtrl.$inject.concat([ 'OpenQuestionService' ]);

// Register controller into AngularJS
angular
    .module('Correction')
    .controller('OpenCorrectionCtrl', OpenCorrectionCtrl);
