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
                        $scope.portfolios.$resolved = true;
                    },
                    function(errorPayload) {
                        console.error('failure loading movie', errorPayload);
                    });
        };

        $scope.selectPortolio = function(portfolioId) {
            $scope.selectedPortfolio = portfolioId;
        };
    }]);