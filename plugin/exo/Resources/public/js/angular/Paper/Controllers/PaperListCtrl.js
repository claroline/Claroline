/**
 * List all the Papers of an Exercise
 * @param {Object}           $filter
 * @param {CommonService}    CommonService
 * @param {ExerciseService}  ExerciseService
 * @param {Object}           exercise
 * @param {PaperService}     PaperService
 * @param {UserPaperService} UserPaperService
 * @param {Array}            papers
 * @constructor
 */
var PaperListCtrl = function PaperListCtrl($filter, CommonService, ExerciseService, exercise, PaperService, UserPaperService, papers) {
    this.$filter = $filter;
    this.PaperService  = PaperService;
    this.CommonService = CommonService;
    this.ExerciseService = ExerciseService;
    this.UserPaperService = UserPaperService;

    this.editEnabled = this.ExerciseService.isEditEnabled();
    this.papers    = papers;
    this.exercise  = exercise;

    this.filtered = this.papers;
};

// set up dependency injection
PaperListCtrl.$inject = ['$filter', 'CommonService', 'ExerciseService', 'exercise', 'PaperService', 'UserPaperService', 'papers'];

/**
 * @type {boolean}
 */
PaperListCtrl.prototype.editEnabled = false;

/**
 * Original list of Papers
 * @type {Array}
 */
PaperListCtrl.prototype.papers = [];

/**
 * Current Exercise
 * @type {Object}
 */
PaperListCtrl.prototype.exercise = {};

/**
 * Filtered list of Papers (filtered by `query`)
 * @type {Array}
 */
PaperListCtrl.prototype.filtered = [];

/**
 * Filter query string
 * @type {string}
 */
PaperListCtrl.prototype.query = '';

/**
 * Table and Pagination configuration
 * @type {Object}
 */
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
 * Check whether a Paper needs a manual correction (if the score of one question is -1)
 * @param paper
 */
PaperListCtrl.prototype.needManualCorrection = function needManualCorrection(paper) {
    return this.PaperService.needManualCorrection(paper);
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

/**
 * Check if the correction for the Paper is available
 * @param {Object} paper
 * @returns boolean
 */
PaperListCtrl.prototype.isCorrectionAvailable = function isCorrectionAvailable(paper) {
    return this.UserPaperService.isCorrectionAvailable(paper);
};

/**
 * Check if the score obtained by the User for the Paper is available
 * @param {Object} paper
 * @returns boolean
 */
PaperListCtrl.prototype.isScoreAvailable = function isScoreAvailable(paper) {
    return this.UserPaperService.isScoreAvailable(paper);
};

// Register controller into AngularJS
angular
    .module('Paper')
    .controller('PaperListCtrl', PaperListCtrl);
