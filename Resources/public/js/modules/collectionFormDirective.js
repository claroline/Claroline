'use strict';

appDirectives
    .directive("collectionForm", function() {
        return {
            scope:    true,
            restrict: 'A',
            controller: "collectionFormController"
        };
    });