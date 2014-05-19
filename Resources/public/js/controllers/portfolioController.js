'use strict';

portfolioApp
    .controller('portfolioController', ['$scope', 'portfolioManager', 'widgetsManager', 'urlInterpolator', '$attrs', function($scope, portfolioManager, widgetsManager, urlInterpolator, $attrs) {
        $scope.portfolio      = portfolioManager.getPortfolio($attrs['portfolioContainer']);
        $scope.widgetPortlets = widgetsManager.widgets;
    }]);