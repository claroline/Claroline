(function () {
    'use strict';

    angular.module('Step').controller('StepShowCtrl', [
        'CommonService',
        function (CommonService) {
            
            this.currentStep = {};        
            
            this.setCurrentStep = function (step) {
                this.currentStep = step;
            };

            
        }
    ]);
})();