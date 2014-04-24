'use strict';

portfolioApp
    .controller('PortfolioController', ['$scope', 'PortfolioService', function($scope, PortfolioService) {
        PortfolioService.get({'portfolioId': 1}).$promise.then(function(datas) {
            $scope.portfolio = datas;
        });
    }]);