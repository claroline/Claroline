'use strict';

indexApp
    .controller("indexController", ["$scope", "widgetManager", "assetPath",
        function($scope, widgetManager, assetPath) {
            $scope.assetPath = assetPath;

            $scope.widgets = widgetManager.init();
            $scope.widgets.then(function(widgets) {
                $scope.widgets = widgets;
                $scope.widgets.$resolved = true;
            });
        }
    ]);