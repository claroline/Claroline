(function () {
    'use strict';

    angular.module('FormModule').directive('durationField', [
        function DurationFieldDirective() {
            // Step for each increment on the hour field
            var stepHour = 1;

            // Step for each increment on the minute field
            var stepMinute = 5;

            return {
                restrict: 'E',
                replace: true,
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Form/Partial/duration-field.html',
                scope: {
                    model: '='
                },
                link: function (scope, element, attrs) {
                    scope.$watch('model', function (newValue) {
                        scope.hours   = 0;
                        scope.minutes = 0;

                        if (newValue) {
                            var minutes = parseInt(scope.model / 60);

                            scope.hours   = parseInt(minutes / 60);
                            scope.minutes = parseInt(minutes % 60);
                        }
                    });

                    scope.incrementDuration = function (type) {
                        if ('hour' === type) {
                            scope.hours += stepHour;
                        }
                        else if ('minute' === type && (scope.minutes + stepMinute) < 60) {
                            scope.minutes += stepMinute;
                        }

                        scope.recalculate();
                    };

                    scope.decrementDuration = function (type) {
                        if ('hour' === type && (scope.hours - stepHour) >= 0) { // Negative values are not allowed
                            scope.hours -= stepHour;
                        }
                        else if ('minute' === type && (scope.minutes - stepMinute) >= 0) { // Negative values are not allowed
                            scope.minutes -= stepMinute;
                        }

                        scope.recalculate();
                    };

                    scope.correctDuration = function (type) {
                        // Don't allow negative value, so always return absolute value
                        if ('hour' === type) {
                            scope.hours = Math.abs(scope.hours);
                        }
                        else if ('minute' === type) {
                            scope.minutes = Math.abs(scope.minutes);

                            // Don't allow more than 60 minutes
                            var minutesToHours = Math.floor(scope.minutes / 60);
                            if (minutesToHours > 0) {
                                scope.hours += minutesToHours;
                                scope.minutes = scope.minutes % 60;
                            }
                        }

                        scope.recalculate();
                    };

                    scope.recalculate = function () {
                        scope.model = (scope.hours * 3600) + (scope.minutes * 60);
                    };
                }
            }
        }
    ]);
})();