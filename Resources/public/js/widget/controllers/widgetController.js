'use strict';

widgetsApp
    .controller("widgetController", ["$scope", "widgetManager", "$attrs", "assetPath",
        function($scope, widgetManager, $attrs, assetPath) {
            $scope.widgetType = $attrs['widgetContainer'];

            $scope.widgets = widgetManager.getWidgets($scope.widgetType);
            $scope.widgets.then(function () {
                $scope.widgets = widgetManager.getWidgetsByType($scope.widgetType);
            });

            $scope.assetPath = assetPath;

            $scope.createWidget = function() {
                widgetManager.create($scope.widgetType);
            };
        }
    ]);