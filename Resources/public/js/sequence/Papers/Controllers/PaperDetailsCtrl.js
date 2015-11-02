(function () {
    'use strict';

    angular.module('PapersApp').controller('PaperDetailsCtrl', [
        '$routeParams',
        '$window',
        'paperPromise',
        function ($routeParams, $window, paperPromise) {
            
            this.paper = paperPromise.data.paper;
            this.sequence = paperPromise.data.sequence;
            this.exoId = $routeParams.eid;
            
            
        }
    ]);
})();