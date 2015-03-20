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
            floating:   false, // whether to automatically float items up so they stack (you can temporarily disable if you are adding unsorted items with ng-repeat)
            margins:    [10, 10], // the pixel distance between each widget
            minColumns: 1, // the minimum columns the grid must have
            minRows:    1, // the minimum height of the grid, in rows
            maxRows:    100,
            resizable: {
                enabled: true,
                handles: ['n', 'e', 's', 'w', 'ne', 'se', 'sw', 'nw'],
                start: function(event, $element, widget) {
                    widget.isResized = true;
                }, // optional callback fired when resize is started,
                stop: function(event, $element, widget) {
                    if (!widget.isEditing()) {
                        widgetsManager.save(widget);
                    }
                    widget.isResized = false;
                } // optional callback fired when item is finished resizing
            },
            draggable: {
                enabled: true, // whether dragging items is supported
                handle: '.panel-heading .draggable-handle', // optional selector for resize handle
                start: function(event, $element, widget) {
                    widget.isDragged = true;
                }, // optional callback fired when drag is started,
                stop: function(event, $element, widget) {
                    if (!widget.isEditing()) {
                        widgetsManager.save(widget);
                    }
                    widget.isDragged = false;
                } // optional callback fired when item is finished dragging
            }
        };
    }]);