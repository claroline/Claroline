/**
 * Paper details directive controller
 * 
 */
(function () {
    'use strict';

    angular.module('Correction').controller('CorrectionClozeCtrl', [
        'CommonService',
        'CorrectionService',
        '$timeout',
        function (CommonService, CorrectionService, $timeout) {

            this.question = {};
            this.paper = {};
                    
            $timeout(function () {
                console.log("load");
                var inputs = document.getElementsByClassName('blank');
                for (var i=0; i<inputs.length; i++) {
                    inputs[i].setAttribute("disabled", true);
                }
            });

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
                
                console.log(this.question);
                console.log(this.paper);
            };

            

        }
    ]);
})();