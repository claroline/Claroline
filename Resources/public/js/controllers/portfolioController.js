'use strict';

portfolioApp
    .controller("portfolioController", ["$scope", "portfolioManager", "widgetsManager", "urlInterpolator", "$attrs", "widgetsConfig", function($scope, portfolioManager, widgetsManager, urlInterpolator, $attrs, widgetsConfig) {
        $scope.portfolio      = portfolioManager.getPortfolio($attrs['portfolioContainer']);
        $scope.widgetPortlets = widgetsManager.widgets;
        $scope.widgetTypes    = widgetsConfig.getTypes(true);

        $scope.createWidget = function(type) {
            widgetsManager.create(portfolioManager.portfolioId, type);
        }
    }]);