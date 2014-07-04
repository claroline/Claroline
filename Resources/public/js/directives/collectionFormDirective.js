'use strict';

portfolioApp
    .directive("collectionForm", function() {
        return {
            scope:    true,
            restrict: 'A',
            controller: "collectionFormController"
        };
    });