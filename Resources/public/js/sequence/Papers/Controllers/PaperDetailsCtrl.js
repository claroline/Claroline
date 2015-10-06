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
            
            $scope.paper = paperPromise.data;

        }
    ]);
})();