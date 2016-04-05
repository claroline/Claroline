'use strict';

indexApp
    .directive("indexContainer", function() {
        return {
            restrict: "AC",
            controller: "indexController",
            scope: true
        };
    });