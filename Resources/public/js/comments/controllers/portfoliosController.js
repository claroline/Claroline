'use strict';

commentsApp
    .controller("portfoliosController", ["$scope", "$timeout", function($scope, $timeout) {
        $scope.selectedPortfolio = window.currentPortfolio;
        $scope.portfolios = [
            {
                'id': 1,
                'title': 'pouet',
                'unreadComments': 2
            },
            {
                'id': 2,
                'title': 'ethsdtghsfd',
                'unreadComments': 0
            }
        ];

        $scope.init = function () {
            if (0 < this.selectedPortfolio) {
                $timeout(function () {
                    $scope.portfolios.$resolved = true;
                }, 2000);
            }
        };

        $scope.selectPortolio = function(portfolioId) {
            $scope.selectedPortfolio = portfolioId;
        };
    }]);