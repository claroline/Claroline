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
            columns:           6, // the width of the grid, in columns
            margins:           [10, 10], // the pixel distance between each widget
            minColumns:        1, // the minimum columns the grid must have
            minRows:           1, // the minimum height of the grid, in rows
            maxRows:           100,
            resizable: {
               enabled: true,
               handles: ['n', 'e', 's', 'w', 'ne', 'se', 'sw', 'nw'],
               stop: function(event, $element, widget) {
                   widgetsManager.save(widget);
               } // optional callback fired when item is finished resizing
            },
            draggable: {
               enabled: true, // whether dragging items is supported
               handle: '.panel-heading', // optional selector for resize handle
               stop: function(event, $element, widget) {
                   widgetsManager.save(widget);
               } // optional callback fired when item is finished dragging
            }
        };
    }]);