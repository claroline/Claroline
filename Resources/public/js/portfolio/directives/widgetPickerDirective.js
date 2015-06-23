'use strict';

portfolioApp
    .directive('widgetPicker', function () {
        var url = Routing.generate("icap_portfolio_widget_picker");
        var data = {
            multiple: true
        };
        var successCallback = function(nodes) {
            console.log(nodes);
            return null;
        };

        return {
            restrict: "A",
            link: function ($scope, element, attrs) {
                var customData = {};
                var successCallback = successCallback;
                var widgetType = attrs.widgetType;

                if (attrs.widgetPicker) {
                    var widgetpickerConfig = $scope.$eval(attrs.widgetPicker);
                    customData = widgetpickerConfig.data || {};
                    successCallback = widgetpickerConfig.successCallback || successCallback;
                }
                angular.extend(data, customData);

                var closeCallback = function(nodes) {
                    var newSelectedValue = [];
                    angular.forEach(nodes, function (element, index) {
                        newSelectedValue.push(element.id);
                    });

                    this.data.value = newSelectedValue;
                };

                // Initialize widget picker object
                window.Claroline.WidgetPicker.configureWidgetPicker(url, data, successCallback, closeCallback);

                $scope.widgetPickerOpen = function () {
                    window.Claroline.WidgetPicker.openWidgetPicker();
                };
                element[0].onclick = function (event) {
                    event.preventDefault();
                    $scope.widgetPickerOpen();
                };
            }
        };
    });