(function () {
    'use strict';

    angular.module('Page').controller('PageShowCtrl', [
        'PageService',
        function (PageService) {
            

            this.sayHello = function () {
                console.log(PageService.hello());
            };
        }
    ]);
})();