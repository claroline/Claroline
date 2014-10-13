'use strict';

portfolioApp
    .controller("portfolioController", ["$scope", "$filter", "portfolioManager", "widgetsManager", "commentsManager", "$attrs", "widgetsConfig", "assetPath", "$timeout",
                                function($scope, $filter, portfolioManager, widgetsManager, commentsManager, $attrs, widgetsConfig, assetPath, $timeout) {
        $scope.portfolio = portfolioManager.getPortfolio($attrs['portfolioContainer']);
        $scope.portfolio.$promise.then(function () {
            $scope.widgets  = widgetsManager.widgets;
            $scope.comments = commentsManager.comments;
        });

        $scope.widgetTypes    = widgetsConfig.getTypes(true);
        $scope.assetPath      = assetPath;

        $scope.createWidget = function(type) {
            widgetsManager.create(portfolioManager.portfolioId, type);
        }
    }]);