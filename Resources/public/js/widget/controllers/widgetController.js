'use strict';

widgetsApp
    .controller("widgetController", ["$scope", "widgetManager", "$attrs",
        function($scope, widgetManager, $attrs) {
            $scope.widgetType = $attrs['widgetContainer'];

            $scope.createWidget = function() {
                widgetManager.create($scope.widgetType);
            };

            $scope.edit = function(widget) {
                widgetManager.edit(widget);
            };
        }
    ]);