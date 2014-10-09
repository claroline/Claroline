'use strict';

portfolioApp
    .controller("portfolioController", ["$scope", "$filter", "portfolioManager", "widgetsManager", "$attrs", "widgetsConfig", "assetPath", function($scope, $filter, portfolioManager, widgetsManager, $attrs, widgetsConfig, assetPath) {
        $scope.portfolio = portfolioManager.getPortfolio($attrs['portfolioContainer']);
        $scope.portfolio.$promise.then(function () {
            $scope.widgets = widgetsManager.widgets;
        });

        $scope.widgetTypes = widgetsConfig.getTypes(true);
        $scope.assetPath      = assetPath;
        $scope.displayComment = true;
        $scope.comment        = "";

        $scope.createWidget = function(type) {
            widgetsManager.create(portfolioManager.portfolioId, type);
        }
        $scope.addComment = function() {
            if (this.comment) {
                var comment = {
                    'message' : this.comment
                };
                this.portfolio.comments.push(comment);
                portfolioManager.save(this.portfolio);
                this.comment = '';
            }
        };
    }]);