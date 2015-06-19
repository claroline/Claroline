'use strict';

angular.module('ui.badgePicker', [])
    .directive('uiBadgePicker', function () {
        var url = Routing.generate("icap_badge_badge_picker");
        var data = {
            mode: "user"
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
                if (attrs.uiBadgePicker) {
                    var badgepickerConfig = $scope.$eval(attrs.uiBadgePicker);
                    customData     = badgepickerConfig.data || {};
                    successCallback = badgepickerConfig.successCallback || successCallback;
                }
                angular.extend(data, customData);

                var closeCallback = function(nodes) {
                    var newSelectedValue = [];
                    angular.forEach(nodes, function (element, index) {
                        newSelectedValue.push(element.id);
                    });

                    this.data.value = newSelectedValue;
                };

                // Initialize badge picker object
                window.Claroline.BadgePicker.configureWidgetPicker(url, data, successCallback, closeCallback);

                $scope.badgePickerOpen = function () {
                    window.Claroline.BadgePicker.openWidgetPicker();
                };

                element[0].onclick = function (event) {
                    event.preventDefault();
                    $scope.badgePickerOpen();
                };
            }
        };
    });