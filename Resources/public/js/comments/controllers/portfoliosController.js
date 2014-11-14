'use strict';

commentsApp
    .controller("portfoliosController", ["$scope", "portfolioManager", "commentsManager", function($scope, portfolioManager, commentsManager) {
        $scope.selectedPortfolio = window.currentPortfolio;
        $scope.portfolios = [];

        $scope.init = function () {
            var promise = portfolioManager.getPortfolios();
            promise.then(
                function(data) {
                    $scope.portfolios = data;
                    commentsManager.init($scope.selectedPortfolio);
                },
                function(errorPayload) {
                    console.error('failure loading portfolios', errorPayload);
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