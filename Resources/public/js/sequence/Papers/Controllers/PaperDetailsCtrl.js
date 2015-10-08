(function () {
    'use strict';

    angular.module('PapersApp').controller('PaperDetailsCtrl', [
        '$scope',
        '$routeParams',
        '$window',
        'PapersService',
        'CommonService',
        'paperPromise',
        function ($scope, $routeParams, $window, PapersService, CommonService, paperPromise) {
            
            this.paper = paperPromise.data;
            this.exoId = $routeParams.eid;
        }
    ]);
})();