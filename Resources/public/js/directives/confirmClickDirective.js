'use strict';

portfolioApp
    .directive('confirmClick', ["$parse", function ($parse) {
        function link(scope, element, attributes) {
            var clickAction = attributes.confirmClick;
            element.confirmModal({'confirmCallback': function() {scope.$eval(clickAction)}});
        }

        var directive = {
            link: link,
            restrict: 'A'
        };

        return directive;
    }]);