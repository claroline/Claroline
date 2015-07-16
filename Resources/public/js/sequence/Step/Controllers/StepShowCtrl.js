(function () {
    'use strict';

    angular.module('Step').controller('StepShowCtrl', [
        'StepService',
        function (StepService) {
            

            this.sayHello = function (name) {            
                console.log(StepService.hello(name));
            };
        }
    ]);
})();