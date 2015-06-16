'use strict';

widgetsApp
    .controller("widgetController", ["$scope", "widgetManager", "$attrs",
        function($scope, widgetManager, $attrs) {
            $scope.widgetType = $attrs['widgetContainer'];

            $scope.create = function() {
                widgetManager.create($scope.widgetType);
            };

            $scope.edit = function(widget) {
                widgetManager.edit(widget);
            };

            $scope.delete = function(widget) {
                widgetManager.delete(widget);
            };
        }
    ]);