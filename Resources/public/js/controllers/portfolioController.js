'use strict';

portfolioApp
    .controller("portfolioController", ["$scope", "$filter", "portfolioManager", "widgetsManager", "$attrs", "widgetsConfig", "assetPath", "$timeout",
                                function($scope, $filter, portfolioManager, widgetsManager, $attrs, widgetsConfig, assetPath, $timeout) {
        $scope.portfolio = portfolioManager.getPortfolio($attrs['portfolioContainer']);
        $scope.portfolio.$promise.then(function () {
            $scope.widgets = widgetsManager.widgets;
        });

        $scope.widgetTypes    = widgetsConfig.getTypes(true);
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
                var wrapper = $(".communication_panel .media-list")[0];

                this.portfolio.comments.push(comment);
                portfolioManager.save(this.portfolio);
                this.comment = '';


                // will fail if you scroll immediately because we scroll before the view is updated;
                $timeout(function(){
                  wrapper.scrollTop = wrapper.scrollHeight;
                },0);
            }
        };
    }]);