'use strict';

portfolioApp
    .controller("widgetsController", ["$scope", "$attrs", "widgetsManager", function($scope, $attrs, widgetsManager) {
        $scope.type    = $scope.widgetType;
        $scope.widgets = [];

        $scope.$watch("widgetPortlets." + $scope.type, function(data) {
            if (data) {
                $scope.widgets = data;
            }
        });

        $scope.edit = function(widget) {
            widgetsManager.edit(widget);
        };

        $scope.isDeletable = function(widget) {
            return widgetsManager.isDeletable(widget);
        };
    }]);