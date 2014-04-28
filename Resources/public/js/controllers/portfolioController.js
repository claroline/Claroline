'use strict';

portfolioApp
    .controller('portfolioController', ['$scope', 'portfolioManager', 'widgetsManager', 'urlInterpolator', function($scope, portfolioManager, widgetsManager, urlInterpolator) {
        $scope.portfolio      = portfolioManager.getPortfolio(1);
        $scope.widgetPortlets = widgetsManager.widgets;
    }]);