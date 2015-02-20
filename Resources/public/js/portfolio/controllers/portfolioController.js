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

        $scope.createWidget = function(type, column) {
            widgetsManager.create(portfolioManager.portfolioId, type, column || 1);
        };

        $scope.gridsterOptions = {
            columns:    6, // the width of the grid, in columns
            swapping:   true, // whether or not to have items of the same size switch places instead of pushing down if they are the same size
            floating:   true, // whether to automatically float items up so they stack (you can temporarily disable if you are adding unsorted items with ng-repeat)
            margins:    [10, 10], // the pixel distance between each widget
            minColumns: 1, // the minimum columns the grid must have
            minRows:    1, // the minimum height of the grid, in rows
            maxRows:    100,
            resizable: {
               enabled: false,
               handles: ['n', 'e', 's', 'w', 'ne', 'se', 'sw', 'nw']
            },
            draggable: {
               enabled: true, // whether dragging items is supported
               handle: '.panel-heading', // optional selector for resize handle
               start: function(event, $element, widget) {
                   console.log('start dragging');
                   console.log($element);
                   console.log(widget);
               }, // optional callback fired when drag is started,
               stop: function(event, $element, widget) {
                   widgetsManager.save(widget);

                   //for (var index = 0; index < $scope.widgets.length; index++) {
                   //    widgetsManager.save($scope.widgets[index]);
                   //}
               } // optional callback fired when item is finished dragging
            }
        };

        $scope.toggleDisposition = function() {
            //$scope.gridsterOptions.draggable.enabled = !$scope.gridsterOptions.draggable.enabled;
            //$scope.gridsterOptions.resizable.enabled = !$scope.gridsterOptions.resizable.enabled;
            for (var index = 0; index < $scope.widgets.length; index++) {
               widgetsManager.save($scope.widgets[index]);
            }
        };

        $scope.toggleDisposition2 = function() {
            $scope.$apply();
        };
    }]);