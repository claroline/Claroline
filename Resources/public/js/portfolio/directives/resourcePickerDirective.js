'use strict';

angular.module('ui.resourcePicker', [])
    .directive('uiResourcePicker', [function () {

        // Set some default options
        var defaultOptions = {
            name:                       null,
            isPickerMultiSelectAllowed: false,
            isPickerOnly:               true,
            isWorkspace:                true,
            resourceTypes:              window.resourceTypes,
            callback: function (nodes) {
                console.log(nodes);
                return null;
            }
        };

        return {
            scope: {
                uiResourcePicker: '=',
                uiResources: '='
            },
            restrict: "A",
            link: function ($scope, element, attrs) {
                var uiResourcePickerConfiguration = ($scope.uiResourcePicker) ? $scope.uiResourcePicker : {};

                var options = angular.extend({}, defaultOptions, uiResourcePickerConfiguration)

                if (typeof options.name === 'undefined' || options.name === null || options.name.length === 0 ) {
                    // Generate unique name
                    options.name = 'picker-' + Math.floor(Math.random() * 10000);
                }

                if (!attrs.id) {
                    attrs.$set('id', options.name);
                }
                else {
                    // Reuse existing id as picker name
                    options.name = attrs.id;
                }

                // Initialize resource picker object
                if (!Claroline.ResourceManager.hasPicker(options.name)) {
                    Claroline.ResourceManager.createPicker(options.name, options, false);
                }

                $scope.resourcePickerOpen = function (pickerName) {
                    // Initialize resource picker object
                    Claroline.ResourceManager.picker(pickerName, 'open');
                };

                $scope.resourcePickerClose = function (pickerName) {
                    Claroline.ResourceManager.picker(pickerName, 'close');
                };

                element[0].onclick = function (event) {
                    event.preventDefault();
                    $scope.resourcePickerOpen(this.id);
                };
            }
        };
    }]);