'use strict';

portfolioApp
    .controller("widgetsController", ["$scope", "$attrs", "widgetsManager", function($scope, $attrs, widgetsManager) {
        $scope.type    = $attrs.widgetPortlet;
        $scope.widgets = [];

        $scope.$watch("widgetPortlets." + $scope.type, function(data) {
            if (data) {
                $scope.widgets = data;
            }
        });

        $scope.edit = function(widget) {
            widgetsManager.edit(widget);
        };
    }]);