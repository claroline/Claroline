'use strict';

portfolioApp
    .controller('PortfolioController', ['$scope', 'PortfolioService', function($scope, PortfolioService) {
        $scope.viewPath = "";

        $scope.portfolio = PortfolioService.get({'portfolioId': 1});

        $scope.editTitle = function () {
            console.log($scope.portfolio);
        };
    }]);