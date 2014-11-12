'use strict';

commentsApp
    .directive("portfolioContainer", function() {
        return {
            restrict:   "AC",
            controller: "portfoliosController"
        };
    });