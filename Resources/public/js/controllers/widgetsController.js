'use strict';

portfolioApp
    .controller('widgetsController', ['$rootScope', '$scope', '$attrs', function($rootScope, $scope, $attrs) {
        $scope.type    = $attrs.widgetPortlet;
        $scope.widgets = [];

        $scope.$watch("widgetPortlets." + $scope.type, function(data) {
            $scope.widgets = data;
        });
    }]);