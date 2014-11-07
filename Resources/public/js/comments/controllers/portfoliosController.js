'use strict';

commentsApp
    .controller("portfoliosController", ["$scope", "$timeout", function($scope, $timeout) {
        $scope.selectedPortfolio = window.currentPortfolio;
        $scope.portfolios = [
            {
                'id': 42,
                'title': 'pouet',
                'unreadComments': 2
            },
            {
                'id': 41,
                'title': 'ethsdtghsfd',
                'unreadComments': 0
            }
        ];
        $timeout(function () {
            $scope.portfolios.$resolved = true;
        }, 2000);

        $scope.selectPortolio = function(portfolioId) {
            $scope.selectedPortfolio = portfolioId;
        };
    }]);