(function () {
    'use strict';

    angular.module('PapersApp').controller('PaperListCtrl', [
        '$routeParams',
        '$window',
        '$filter',
        'CommonService',
        'paperListPromise',
        function ($routeParams, $window, $filter, CommonService, paperListPromise) {

            this.papers = paperListPromise.data.papers;
            this.sequence = paperListPromise.data.sequence;
            this.filtered = this.papers;
            this.query = '';
            this.exoId = $routeParams.eid;
            this.showPagination = true;

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
             * Checks if we can display the correction link
             * @returns {bool}
             */
            this.checkCorrectionAvailability = function (paper) {
                var correctionMode = CommonService.getCorrectionMode(this.sequence.meta.correctionMode);             
                var nbFinishedAttempts = this.countFinishedAttempts();
                switch (correctionMode) {
                    case "test-end":
                        return paper.end && paper.end !== undefined && paper.end !== '' ;
                        break;
                    case "last-try":
                        // number of paper with date end === sequence.maxAttempts ?
                        return nbFinishedAttempts === this.sequence.meta.maxAttempts;
                        break;
                    case "after-date":
                        var current = new Date();
                        // compare with ??? sequence.endDate ?
                        return true;
                        break;
                    case "never":
                        return false;
                        break;
                    default:
                        return false;
                }
            };
            
            this.countFinishedAttempts = function (){
                var nb = 0;
                for(var i = 0; i < this.papers.length; i++){
                    if(this.papers[i].end && this.papers[i].end !== undefined && this.papers[i].end !== ''){
                        nb++;
                    }
                }
                return nb;
            };


        }
    ]);
})();