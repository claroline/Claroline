'use strict';

statisticsApp
    .directive("statisticsViewContainer", function() {
        return {
            restrict:   "AC",
            scope: true,
            controller: "statisticsViewController"
        };
    });