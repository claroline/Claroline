(function () {
    'use strict';

    angular.module('Page').controller('PageShowController', [
        'PageService',
        function (PageService) {
            

            this.sayHello = function () {
                console.log(PageService.hello());
            };
        }
    ]);
})();