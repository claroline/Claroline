/**
 * Paper Show Controller
 * Displays the details of a Paper
 * @param {Object} paperPromise
 * @param {PaperService} PaperService
 * @constructor
 */
var PaperShowCtrl = function PaperShowCtrl(paperPromise, PaperService) {
    this.PaperService = PaperService;

    this.paper        = paperPromise.paper;
    this.questions    = this.PaperService.orderQuestions(this.paper, paperPromise.questions);
};

// Set up dependency injection
PaperShowCtrl.$inject = [ 'paperPromise', 'PaperService' ];

PaperShowCtrl.prototype.paper = {};

/**
 * Ordered Questions of the Paper
 * @type {Array}
 */
PaperShowCtrl.prototype.questions = [];

PaperShowCtrl.prototype.getQuestionPaper = function getQuestionPaper(question) {
    return this.PaperService.getQuestionPaper(this.paper, question);
};

/**
 * Get the score of a Paper
 * @returns {Number}
 */
PaperShowCtrl.prototype.getScore = function getScore() {
    return this.PaperService.getPaperScore(this.paper);
};

// Register controller into Angular JS
angular
    .module('Paper')
    .controller('PaperShowCtrl', PaperShowCtrl);