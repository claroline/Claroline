'use strict';

widgetsApp
    .directive("widgetContainer", function() {
        return {
            restrict: "AC",
            controller: "widgetController",
            scope: true
        };
    });