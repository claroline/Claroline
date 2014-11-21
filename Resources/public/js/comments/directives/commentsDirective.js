'use strict';

commentsApp
    .directive("commentContainer", function() {
        return {
            restrict:   "AC",
            controller: "commentsController"
        };
    });