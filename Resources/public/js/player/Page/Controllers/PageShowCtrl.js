(function () {
    'use strict';

    angular.module('Page').controller('PageShowCtrl', [
        'PageService',
        function (PageService) {
            

            this.sayHello = function (name) {            
                console.log(PageService.hello(name));
            };
        }
    ]);
})();