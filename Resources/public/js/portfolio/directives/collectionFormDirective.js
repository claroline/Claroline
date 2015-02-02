'use strict';

portfolioApp
    .directive("collectionForm", function() {
        return {
            scope:    {
                'collectionForm': '@collectionForm',
                'widget': '=collectionFormWidget'
            },
            restrict: 'A',
            controller: "collectionFormController",
            templateUrl: 'templates/collection-form_directive.tpl.html'
        };
    });