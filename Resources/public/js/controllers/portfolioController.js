'use strict';

portfolioApp
    .controller("portfolioController", ["$scope", "$filter", "portfolioManager", "widgetsManager", "$attrs", "widgetsConfig", "assetPath", function($scope, $filter, portfolioManager, widgetsManager, $attrs, widgetsConfig, assetPath) {
        $scope.portfolio = portfolioManager.getPortfolio($attrs['portfolioContainer']);
        $scope.portfolio.$promise.then(function () {
            $scope.widgets = widgetsManager.widgets;
        });
        $scope.widgetTypes = widgetsConfig.getTypes(true);

        $scope.createWidget = function(type) {
            widgetsManager.create(portfolioManager.portfolioId, type);
        }
        $scope.assetPath = assetPath;
    }]);