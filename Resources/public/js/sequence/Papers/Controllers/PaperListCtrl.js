(function () {
    'use strict';

    angular.module('PapersApp').controller('PaperListCtrl', [
        '$routeParams',
        '$window',
        '$filter',
        'CommonService',
        'paperListPromise',
        function ($routeParams, $window, $filter, CommonService, paperListPromise) {

            this.papers = paperListPromise.data;
            this.filtered = this.papers;
            this.query = '';
            this.exoId = $routeParams.eid;
            this.showPagination = true;
            this.itemPerPageDefaultValue = 5;
          
            this.config = {
                itemsPerPage: this.itemPerPageDefaultValue,
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


        }
    ]);
})();