(function () {
    'use strict';

    angular.module('Question').controller('MatchQuestionProposalCtrl', [
        function () {
            this.proposal = {};
            
            this.init = function (proposal) {
                this.proposal = proposal;
            };
        }
    ]);
})();