'use strict';

portfolioApp
    .controller("portfolioController", ["$scope", "portfolioManager", "widgetsManager", "$attrs", "widgetsConfig", function($scope, portfolioManager, widgetsManager, $attrs, widgetsConfig) {
        $scope.portfolio      = portfolioManager.getPortfolio($attrs['portfolioContainer']);
        $scope.widgetPortlets = widgetsManager.widgets;
        $scope.widgetTypes    = widgetsConfig.getTypes(true);

        $scope.createWidget = function(type) {
            widgetsManager.create(portfolioManager.portfolioId, type);
        }
    }]);