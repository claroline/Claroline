(function() {
    'use strict';

    angular
        .module('ui.resourcePicker')
        .directive('uiResourcePicker', uiResourcePicker);

    uiResourcePicker.$inject = ['uiResourcePickerConfig', 'website.data'];

    function uiResourcePicker(uiResourcePickerConfig, websiteData) {
        uiResourcePickerConfig = uiResourcePickerConfig || {};
        var directive = {
            restrict: 'A',
            link: link
        };

        return directive;
        ///////////////////////////

        function link($scope, element, attrs) {
            // Set some default options
            var options = {
                isPickerMultiSelectAllowed: false,
                isPickerOnly: true,
                isWorkspace: true,
                resourceTypes: websiteData.resourceTypes,
                callback: function (nodes) {
                    return null;
                }
            };

            if (attrs.uiResourcePicker) {
                var expression = $scope.$eval(attrs.uiResourcePicker);
            } else {
                var expression = {};
            }
            angular.extend(options, uiResourcePickerConfig, expression);
            if ( typeof options.name === 'undefined' || options.name === null || options.name.length === 0 ) {
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
                Claroline.ResourceManager.createPicker(options.name, options);
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
        };
    }
})();