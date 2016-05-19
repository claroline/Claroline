/**
 * Create a Timer
 * @constructor
 */
var TimerDirective = function TimerDirective() {
    return {
        restrict: 'E',
        replace: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Common/Partials/timer.html',
        bindToController: true,
        controllerAs: 'timerCtrl',
        controller: [
            '$localStorage',
            '$timeout',
            function TimerCtrl($localStorage, $timeout) {
                console.log('append directive');

                this.$localStorage = $localStorage;
                this.$timeout      = $timeout;

                // Initialize var
                this.$localStorage.$default({
                    counter: 0,
                    hours: 0,
                    minutes: 0,
                    seconds: 0
                });

                // Function to increase te timer
                var onTimeout = function onTimeout() {
                    // Increase the timer
                    this.$localStorage.counter = $localStorage.counter + 1;

                    // Calculate remaining duration
                    var remaining = this.duration - this.$localStorage.counter;

                    // Transform counter into hours, minutes and seconds
                    var hoursMod = remaining % 3600; // Hours which are not plain
                    this.$localStorage.hours = (remaining - hoursMod) / 3600;

                    var minutesMod = hoursMod % 60; // Minutes which are not plain
                    this.$localStorage.minutes = (hoursMod - minutesMod) / 60;

                    this.$localStorage.seconds = minutesMod;

                    // If timer reach the exercise duration
                    if (this.$localStorage.counter == this.duration) {
                        // Cancel timeout function
                        this.$timeout.cancel(onTimeout);

                        // Remove local storage
                        this.$localStorage.$reset({
                            counter: 0,
                            hours: 0,
                            minutes: 0,
                            seconds: 0
                        });

                        // Execute registered callback
                        this.onEnd();
                    } else {
                        // Call function to increase next
                        this.$timeout(onTimeout.bind(this), 1000);
                    }
                }.bind(this);

                this.destroy = function destroy() {
                    this.$timeout.cancel(onTimeout);
                };

                // Call for the first time the function to increase timer
                this.$timeout(onTimeout.bind(this), 1000);
            }
        ],
        link: function link(scope, element, attrs, ctrl) {
            scope.$on('$destroy', function onDestroy() {
                ctrl.destroy();
            });
        },
        scope: {
            /**
             * Duration of the Timer (in seconds)
             */
            duration : '=',

            /**
             * Callback to execute when the Timer end
             */
            onEnd    : '&'
        }
    };
};

// Set up dependency injection
TimerDirective.$inject = [];

// Register directive into AngularJS
angular
    .module('Common')
    .directive('timer', TimerDirective);