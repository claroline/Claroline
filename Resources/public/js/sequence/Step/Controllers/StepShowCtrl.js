(function () {
    'use strict';

    angular.module('Step').controller('StepShowCtrl', [
        'CommonService',
        function (CommonService) {            
            this.currentStep = {}; 
        }
    ]);
})();