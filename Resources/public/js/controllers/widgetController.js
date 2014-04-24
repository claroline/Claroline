'use strict';

portfolioApp
    .controller('WidgetController', ['$scope', function($scope, $exceptionHandler) {
        $scope.type = "";
        $scope.data = "";

        var init = function() {
//            console.log($scope.widget);
            if (typeof $scope.widget === "undefined") {
                $exceptionHandler("The WidgetController must be initialized with a widget in scope");
            }
            $scope.data = $scope.widget;
            // continue iniitialization
//            console.log($scope);
        };

        init();
    }]);