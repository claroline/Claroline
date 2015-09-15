(function () {
    'use strict';

    angular.module('Step').controller('StepShowCtrl', [
        'CommonService',
        function (CommonService) {
            
            this.currentStep = {};
            
            // on step square click or called by getNext/getPrevious Step
            this.setCurrentStep = function (step) {
                this.currentStep = step;
            };
            
        }
    ]);
})();