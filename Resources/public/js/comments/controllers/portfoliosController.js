'use strict';

commentsApp
    .controller("portfoliosController", ["$scope", "portfolioManager", "commentsManager", "$filter",
        function($scope, portfolioManager, commentsManager, $filter) {
        $scope.selectedPortfolioId = window.currentPortfolioId;
        $scope.selectedPortfolio   = null;
        $scope.portfolios          = portfolioManager.getPortfolios();
        $scope.portfolios.then(
            function(data) {
                $scope.portfolios = data;
                $scope.clickOnPortolio($scope.selectedPortfolioId);
            },
            function(errorPayload) {
                console.error('failure loading portfolios', errorPayload);
            }
        );

        $scope.clickOnPortolio = function(portfolioId) {
            if (null === $scope.selectedPortfolio) {
                $scope.selectedPortfolioId = portfolioId;
                $scope.selectedPortfolio  = $filter('filter')($scope.portfolios, {id: $scope.selectedPortfolioId})[0];
                commentsManager.loadComments(portfolioId);
                $scope.updateCountViewComments();
            }
            else {
                $scope.selectedPortfolioId = 0;
                $scope.selectedPortfolio = null;
            }
        };

        $scope.updateCountViewComments = function () {
            if (0 < $scope.selectedPortfolio.unreadComments) {
                portfolioManager.updateViewCommentsDate($scope.selectedPortfolio);
            }
        };
    }]);