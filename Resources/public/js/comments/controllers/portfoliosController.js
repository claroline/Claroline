'use strict';

commentsApp
    .controller("portfoliosController", ["$scope", function($scope) {
        $scope.selectedPortfolio = null;
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

        $scope.selectPortolio = function(portfolioId) {
            $scope.selectedPortfolio = portfolioId;
        };
    }]);