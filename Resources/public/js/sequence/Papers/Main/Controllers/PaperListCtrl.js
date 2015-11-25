(function () {
    'use strict';

    angular.module('PapersApp').controller('PaperListCtrl', [
        '$route',
        '$filter',
        'CommonService',
        'paperExercise',
        'PapersService',
        'user',
        function ($route, $filter, CommonService, paperExercise, PapersService, user) {

            this.papers = {};//paperList.papers;
            this.questions = {};//paperList.questions;
            this.exercise = paperExercise;
            this.user = user;
            this.displayRetryExerciseLink = false;

            // table data
            this.filtered = {};//this.papers;
            this.query = '';
            this.showPagination = true;


            // initiate paper list depending on user profil
            if ($route.current.params && $route.current.params.eid) {
                var promise = PapersService.getAll($route.current.params.eid, user);
                promise.then(function (result) {
                    this.papers = result.papers;
                    this.questions = result.questions;
                    this.filtered = this.papers;
                    this.setTableData();
                    this.showHideRetryLink();
                }.bind(this));
            }


            // table config
            this.config = {
                itemsPerPage: 10,
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

            this.generateUrl = function (witch, _id) {
                switch (witch) {
                    case 'papers-docimolgy':
                        var nbPapers = this.papers.length;
                        return Routing.generate('ujm_exercise_docimology', {id: _id, nbPapers: nbPapers});
                        break;
                    case 'papers-csv-export':
                        return Routing.generate('ujm_paper_export_results', {exerciseId: _id});
                        break;
                    default:
                        return CommonService.generateUrl(witch, _id);
                }
            };

            this.updateFilteredList = function () {
                this.filtered = $filter("filter")(this.papers, this.query);
            };

            this.togglePaginationButton = function () {
                this.showPagination = !this.showPagination;
                if (!this.showPagination) {
                    this.config.itemsPerPage = this.papers.length;
                }
            };

            /**
             * Checks if current user can replay the exercise
             * Basicaly if user is admin he will always have access to the button
             * @returns {Boolean}
             */
            this.showHideRetryLink = function () {
                if (this.user.admin || this.exercise.meta.maxAttempts === 0) {
                    this.displayRetryExerciseLink = true;
                } else {
                    this.displayRetryExerciseLink = PapersService.userCanReplayExercise(this.exercise.meta.maxAttempts, this.papers);
                }
            };

            /**
             * Checks if we can display the correction link
             * For now the API does not return the needed data so...
             * @returns {bool}
             */
            this.checkCorrectionAvailability = function (paper) {
                var correctionMode = CommonService.getCorrectionMode(this.exercise.meta.correctionMode);
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
           

            this.countFinishedAttempts = function () {
                var nb = 0;
                for (var i = 0; i < this.papers.length; i++) {
                    if (this.papers[i].end && this.papers[i].end !== undefined && this.papers[i].end !== '') {
                        nb++;
                    }
                }
                return nb;
            };

            /**
             * All data that need to be transformed and used in filter / sort
             * @returns {undefined}
             */
            this.setTableData = function () {
                for (var i = 0; i < this.filtered.length; i++) {
                    // set scores in paper object and in the same time format end date
                    if (this.filtered[i].end) { // TODO check score availability
                        this.filtered[i].endDate = $filter('mySqlDateToLocalDate')(this.filtered[i].end);// $filter('toLocalDate')(this.filtered[i].end);// d.toLocaleString();
                        this.filtered[i].score = PapersService.getPaperScore(this.filtered[i], this.questions) + '/20';
                    }
                    else {
                        this.filtered[i].endDate = '-';
                        this.filtered[i].score = '-';
                    }
                    // format start date
                    this.filtered[i].startDate = $filter('mySqlDateToLocalDate')(this.filtered[i].start);

                    // set interrupt property in a human readable way
                    if (this.filtered[i].interrupted) {
                        this.filtered[i].interruptLabel = Translator.trans('paper_list_table_interrupted_yes', {}, 'ujm_sequence');
                    } else {
                        this.filtered[i].interruptLabel = Translator.trans('paper_list_table_interrupted_no', {}, 'ujm_sequence');
                    }
                }
            };
        }
    ]);
})();