'use strict';

commentsApp
    .controller("portfoliosController", ["$scope", "portfolioManager", function($scope, portfolioManager) {
        $scope.selectedPortfolio = window.currentPortfolio;
        $scope.portfolios = [];

        $scope.init = function () {
            var promise = portfolioManager.getPortfolios();
            promise.then(
                function(data) {
                    $scope.portfolios = data;
                },
                function(errorPayload) {
                    console.error('failure loading movie', errorPayload);
                });
        };

        $scope.clickOnPortolio = function(portfolioId) {
            if ($scope.selectedPortfolio != portfolioId) {
                $scope.selectedPortfolio = portfolioId;
            }
            else {
                $scope.selectedPortfolio = null;
            }
        };
    }]);