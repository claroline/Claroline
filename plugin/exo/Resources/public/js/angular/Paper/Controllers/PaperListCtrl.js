/**
 * List all the Papers of an Exercise
 * @param {Object} $filter
 * @param {CommonService} CommonService
 * @param {Object} exercise
 * @param {PaperService} PaperService
 * @param {Object} papersPromise
 * @constructor
 */
var PaperListCtrl = function PaperListCtrl($filter, CommonService, exercise, PaperService, papersPromise) {
    this.$filter = $filter;
    this.PaperService  = PaperService;
    this.CommonService = CommonService;
    this.ExerciseService = ExerciseService;

    this.papers    = papersPromise.papers;
    this.questions = papersPromise.questions;
    this.exercise  = exercise;

    this.filtered = this.papers;
    /*this.setTableData();*/
    /*this.needManualCorrection();*/
};

// set up dependency injection
PaperListCtrl.$inject = ['$filter', 'CommonService', 'exercise', 'PaperService', 'papersPromise'];

PaperListCtrl.prototype.papers = [];

PaperListCtrl.prototype.questions = [];

PaperListCtrl.prototype.exercise = {};

PaperListCtrl.prototype.displayManualCorrectionMessage = false;

// table data
PaperListCtrl.prototype.filtered = [];
PaperListCtrl.prototype.query = '';
PaperListCtrl.prototype.showPagination = true;

// table config
PaperListCtrl.prototype.config = {
    itemsPerPage: '10',
    fillLastPage: false,
    paginatorLabels: {
        stepBack: '‹',
        stepAhead: '›',
        jumpBack: '«',
        jumpAhead: '»',
        first: Translator.trans('paper_list_table_first_page_label', {}, 'ujm_sequence'),
        last: Translator.trans('paper_list_table_last_page_label', {}, 'ujm_sequence')
    }
};

/**
 * Filter the list of Papers based on the User search
 */
PaperListCtrl.prototype.filterPapers = function () {
    this.filtered = this.$filter("filter")(this.papers, this.query);
};

/**
 * Checks if we can display the correction link
 * For now the API does not return the needed data so...
 * @returns {bool}
 */
PaperListCtrl.prototype.checkCorrectionAvailability = function (paper) {
    var correctionMode = this.CommonService.getCorrectionMode(this.exercise.meta.correctionMode);
    // !!! countFinishedAttempts dont take the paper user into account...
    var nbFinishedAttempts = this.countFinishedAttempts();
    switch (correctionMode) {
        case "test-end":
            return paper.end && paper.end !== undefined && paper.end !== '';
            break;
        case "last-try":
            // only used for normal user ? if admin i will always see correction link ?
            return nbFinishedAttempts >= this.exercise.meta.maxAttempts;
            break;
        case "after-date":
            var now = new Date();
            var searched = new RegExp('-', 'g');
            var correctionDate = new Date(Date.parse(this.exercise.meta.correctionDate.replace(searched, '/')));
            return now >= correctionDate;
            break;
        case "never":
            return false;
            break;
        default:
            return false;
    }
};


PaperListCtrl.prototype.countFinishedAttempts = function () {
    var nb = 0;
    for (var i = 0; i < this.papers.length; i++) {
        if (this.papers[i].end && this.papers[i].end !== undefined && this.papers[i].end !== '') {
            nb++;
        }
    }

    return nb;
};

PaperListCtrl.prototype.needManualCorrection = function (){
    for(var i = 0; i < this.questions.length; i++){
        if(this.questions[i].typeOpen && this.questions[i].typeOpen === 'long'){
            this.displayManualCorrectionMessage = true;
            break;
        }
    }
};

/**
 * All data that need to be transformed and used in filter / sort
 * @returns {undefined}
 */
PaperListCtrl.prototype.setTableData = function () {
    var score;
    for (var i = 0; i < this.filtered.length; i++) {
        // set scores in paper object and in the same time format end date
        if (this.filtered[i].end ) { // TODO check score availability
            score = 0;
            score = this.CommonService.getPaperScore(this.filtered[i], this.questions) ;
            if(score !== null){
                this.filtered[i].score = score + '/20';
            } else {
                this.filtered[i].end = '-';
                this.filtered[i].score = '-';
            }
        } else {
            this.filtered[i].end = '-';
            this.filtered[i].score = '-';
        }
        // set interrupt property in a human readable way
        if (this.filtered[i].interrupted) {
            this.filtered[i].interruptLabel = Translator.trans('paper_list_table_interrupted_yes', {}, 'ujm_sequence');
            this.interrupted = true;
        } else {
            this.filtered[i].interruptLabel = Translator.trans('paper_list_table_interrupted_no', {}, 'ujm_sequence');
            this.interrupted = false;
        }
    }
};

/**
 * Delete all Papers of the Exercise
 */
PaperListCtrl.prototype.deletePapers = function deletePapers() {
    this.PaperService.deleteAll(this.papers);
};

/**
 * Delete a Paper
 */
PaperListCtrl.prototype.deletePaper = function deletePaper(paper) {
    this.PaperService.delete(paper);
};

/**
 * Get the score of a Paper
 * @param   {Object} paper
 * @returns {Number}
 */
PaperListCtrl.prototype.getPaperScore = function getPaperScore(paper) {
    return this.PaperService.getPaperScore(paper);
};

// Register controller into AngularJS
angular
    .module('Paper')
    .controller('PaperListCtrl', PaperListCtrl);
