'use strict';

commentsApp
    .controller("portfoliosController", ["$scope", "portfolioManager", "commentsManager", "$filter",
        function($scope, portfolioManager, commentsManager, $filter) {
        $scope.selectedPortfolioId = 0;
        $scope.selectedPortfolio   = null;
        $scope.portfolios          = portfolioManager.getPortfolios();
        $scope.portfolios.then(
            function(data) {
                $scope.portfolios = data;
                $scope.clickOnPortolio(window.currentPortfolioId);
            },
            function(errorPayload) {
                console.error('failure loading portfolios', errorPayload);
            }
        );

        $scope.clickOnPortolio = function(portfolioId) {
            if (portfolioId !== $scope.selectedPortfolioId) {
                $scope.selectedPortfolioId = portfolioId;
                $scope.selectedPortfolio  = $filter('filter')($scope.portfolios, {id: $scope.selectedPortfolioId})[0];
                $scope.updateCountViewComments();
            } else {
                $scope.selectedPortfolioId = 0;
                $scope.selectedPortfolio = null;
            }

            commentsManager.loadComments($scope.selectedPortfolioId);
        };

        $scope.updateCountViewComments = function () {
            if (0 < $scope.selectedPortfolio.unreadComments) {
                portfolioManager.updateViewCommentsDate($scope.selectedPortfolio);
            }
        };
    }]);