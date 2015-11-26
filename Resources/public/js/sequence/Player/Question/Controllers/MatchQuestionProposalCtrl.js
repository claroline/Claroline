(function () {
    'use strict';

    angular.module('Question').controller('MatchQuestionProposalCtrl', [  
        '$ngBootbox',
        'CommonService',        
        'QuestionService',
        function ($ngBootbox, CommonService, QuestionService) {
            this.proposal = {};
            
            this.init = function (proposal) {
                this.proposal = proposal;
            };

            this.setProposal = function (proposal) {
                this.proposal = proposal;
            };

            this.getProposal = function () {
                return this.proposal;
            };
        }
    ]);
})();