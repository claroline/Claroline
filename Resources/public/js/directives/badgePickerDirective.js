'use strict';

angular.module('ui.badgePicker', [])
    .directive('uiBadgePicker', function () {
        var url = Routing.generate("claro_badge_picker");
        var data = {
            mode: "user"
        };
        var callback = function(nodes) {
            console.log(nodes);
            return null;
        };

        return {
            restrict: "A",
            link: function ($scope, element, attrs) {
                var customData = {};
                var customCallback = callback;
                if (attrs.uiBadgePicker) {
                    var badgepickerConfig = $scope.$eval(attrs.uiBadgePicker);
                    customData     = badgepickerConfig.data || {};
                    customCallback = badgepickerConfig.callback || callback;
                }
                angular.extend(data, customData);

                // Initialize badge picker object
                window.Claroline.BadgePicker.configureBadgePicker(url, data, customCallback);

                $scope.badgePickerOpen = function () {
                    window.Claroline.BadgePicker.openBadgePicker();
                };

                element[0].onclick = function (event) {
                    event.preventDefault();
                    $scope.badgePickerOpen();
                };
            }
        };
    });