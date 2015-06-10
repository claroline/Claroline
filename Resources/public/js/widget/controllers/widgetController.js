'use strict';

widgetsApp
    .controller("widgetController", ["$scope", "widgetManager", "$attrs", "assetPath",
        function($scope, widgetManager, $attrs, assetPath) {
            $scope.widgetType = $attrs['widgetContainer'];
            console.log($scope.widgetType);

            $scope.widgets = widgetManager.getWidgets($scope.widgetType);
            $scope.widgets.$promise.then(function (widgets) {
                $scope.widgets = widgets;
            });

            $scope.assetPath = assetPath;

            $scope.createWidget = function() {
                widgetManager.create($scope.widgetType);
            };
        }
    ]);