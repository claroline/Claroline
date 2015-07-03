(function () {
    'use strict';

    angular.module('Page').controller('PageEditCtrl', [
        'PageService',
        function (PageService) {
            

            this.sayHello = function (name) {            
                // console.log(PageService.hello(name));
            };
            
            this.getPage = function (){
                //console.log('called');
                //console.log(PageService.getPage());
                return PageService.getPage();
            };
        }
    ]);
})();