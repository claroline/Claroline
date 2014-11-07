'use strict';

portfolioApp
    .directive("commentsContainer", function() {
        return {
            controller: "commentController"
        };
    });