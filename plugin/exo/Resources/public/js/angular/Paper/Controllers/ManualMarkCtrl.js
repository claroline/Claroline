/**
 * Controller used to let the admins mark questions
 *
 * @param {object} $uibModalInstance
 * @param {PaperService} PaperService
 * @param {Object} question
 * @param {Object} paper
 *
 * @constructor
 */
var ManualMarkCtrl = function ManualMarkCtrl($uibModalInstance, PaperService, question) {
    this.$uibModalInstance = $uibModalInstance;
    this.PaperService = PaperService;

    this.question = question;
};

// Set up dependency injection
ManualMarkCtrl.$inject = [ '$uibModalInstance', 'PaperService', 'question' ];

ManualMarkCtrl.prototype.question = null;

ManualMarkCtrl.prototype.score = null;

/**
 * An error message if the given score is incorrect (eg. greater than the question total score)
 * @type {null}
 */
ManualMarkCtrl.prototype.errors = [];

/**
 * Save mark
 */
ManualMarkCtrl.prototype.save = function save() {
    this.errors.splice(0, this.errors.length);
    if (this.score > this.question.scoreTotal) {
        this.errors.push('mark_bigest');
    } else {
        this.PaperService
            .saveScore(this.question, this.score)
            .then(function () {
                this.score = null;

                // Go back on the paper
                this.$uibModalInstance.close();
            }.bind(this));
    }
};

ManualMarkCtrl.prototype.cancel = function cancel() {
    this.score = null;

    this.$uibModalInstance.dismiss('cancel');
};

angular
    .module('Paper')
    .controller('ManualMarkCtrl', ManualMarkCtrl);