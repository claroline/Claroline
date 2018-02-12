(function () {
    'use strict';

    angular
        .module('ui.resourcePicker', [])
        .directive('btnResourcePicker', [
            function () {
                return {
                    restrict: 'E',
                    replace: true,
                    transclude: true,
                    scope: {
                        parameters : '='
                    },
                    template: '<a href="" role="button" data-ng-click="resourcePickerOpen(pickerName)" data-ng-transclude=""></a>',
                    link: function ($scope, el, attrs) {
                        $scope.pickerName = 'picker-' + Math.floor(Math.random() * 10000);

                        // Watch for parameters change
                        $scope.$watch('parameters', function (newValue) {
                            if (Claroline.ResourceManager.hasPicker($scope.pickerName)) {
                                var picker = Claroline.ResourceManager.get($scope.pickerName);
                                if (angular.isObject(picker)) {
                                    // Update picker parameters
                                    for (var parameter in newValue) {
                                        if (newValue.hasOwnProperty(parameter)) {
                                            picker.parameters[parameter] = newValue[parameter];
                                        }
                                    }
                                }
                            }
                        });

                        $scope.resourcePickerOpen = function (pickerName) {
                            // Initialize resource picker object
                            if (!Claroline.ResourceManager.hasPicker($scope.pickerName)) {
                                Claroline.ResourceManager.createPicker($scope.pickerName, $scope.parameters, true);
                            } else {
                                console.log('picker use directive');
                                // Open existing picker
                                Claroline.ResourceManager.picker(pickerName, 'open');
                            }
                        };

                        $scope.resourcePickerClose = function (pickerName) {
                            Claroline.ResourceManager.picker(pickerName, 'close');
                        };

                        // Destroy instance of picker when directive is destroyed
                        $scope.$on('$destroy', function handleDestroyEvent() {
                            if (Claroline.ResourceManager.hasPicker($scope.pickerName)) {
                                Claroline.ResourceManager.destroy($scope.pickerName);
                            }
                        });
                    }
                };
            }
        ]);
})();
