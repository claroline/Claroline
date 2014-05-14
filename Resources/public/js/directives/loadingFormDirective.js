'use strict';

portfolioApp
    .directive('loadingForm', ["$parse", function ($parse) {
        function link(scope, element, attr) {
            var longOperation = $parse(attr['loadingForm']); // "compile" the bound expression to our directive
            var button        = element.find("button[type=submit]");
            var buttonHtml    = button.html();

            element.on('submit', function (event) {
                scope.$apply(function () {
                    button.attr("disabled", true);
                    button.prepend('<span class="loading"></span> ');

                    longOperation(scope, { $event: event })
                        .then(function (data) {
                            button.attr("disabled", false);
                            button.html(buttonHtml);
                        }, function (res) {
                            button.attr("disabled", false);
                            button.html(buttonHtml);
                        });
                });
            });
        }

        var directive = {
            link: link,
            restrict: 'A'
        };

        return directive;
    }]);