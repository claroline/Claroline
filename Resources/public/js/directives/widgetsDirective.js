'use strict';

portfolioApp
    .directive("widgetPortlet", function() {
        return {
            scope: true,
            controller: "widgetsController"
        };
    });