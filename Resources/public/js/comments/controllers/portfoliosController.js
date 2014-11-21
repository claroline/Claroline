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
            $scope.selectedPortfolioId = 0;
            $scope.selectedPortfolio = null;

            if (portfolioId !== $scope.selectedPortfolioId) {
                $scope.selectedPortfolioId = portfolioId;
                $scope.selectedPortfolio  = $filter('filter')($scope.portfolios, {id: $scope.selectedPortfolioId})[0];
                $scope.updateCountViewComments();
            }

            commentsManager.loadComments(portfolioId);
        };

        $scope.updateCountViewComments = function () {
            if (0 < $scope.selectedPortfolio.unreadComments) {
                portfolioManager.updateViewCommentsDate($scope.selectedPortfolio);
            }
        };
    }]);