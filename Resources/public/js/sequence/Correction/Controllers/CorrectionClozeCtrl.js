/**
 * Paper details directive controller
 * 
 */
(function () {
    'use strict';

    angular.module('Correction').controller('CorrectionClozeCtrl', [
        'CommonService',
        'CorrectionService',
        function (CommonService, CorrectionService) {


            this.question = {};
            this.paper = {};

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
                
                console.log(this.question);
                console.log(this.paper);
            };

            

        }
    ]);
})();