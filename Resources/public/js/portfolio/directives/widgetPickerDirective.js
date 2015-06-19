'use strict';

portfolioApp
    .directive('widgetPicker', function () {
        var url = Routing.generate("icap_portfolio_internal_portfolio_widget_get");
        var data = {
            multi: true
        };

        return {
            restrict: "A",
            link: function ($scope, element, attrs) {
                var customData = {};
                var widgetType = attrs.widgetType;

                if (attrs.widgetPicker) {
                    var widgetPickerConfig = $scope.$eval(attrs.widgetPicker);
                    customData = widgetPickerConfig.data || {};
                }
                angular.extend(data, customData);

                element[0].onclick = function (event) {
                    event.preventDefault();
                };
            }
        };
    });