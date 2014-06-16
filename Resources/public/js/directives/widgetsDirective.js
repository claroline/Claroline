'use strict';

portfolioApp
    .directive("widget", function() {
        return {
            scope: true,
            controller: "widgetsController"
        };
    });