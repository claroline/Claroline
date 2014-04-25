'use strict';

portfolioApp
    .controller('portfolioController', ['$scope', 'portfolioManager', 'widgetsManager', function($scope, portfolioManager, widgetsManager) {
        $scope.portfolio      = portfolioManager.getPortfolio(1);
        $scope.widgetPortlets = widgetsManager.widgets;
    }]);