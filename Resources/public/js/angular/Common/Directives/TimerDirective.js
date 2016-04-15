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
            function TimerCtrl ($localStorage, $timeout) {
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

                    // Call function to increase next
                    this.$timeout(onTimeout.bind(this), 1000);

                    // Transform counter into hours, minutes and second
                    this.$localStorage.hours   = Math.floor((this.duration - this.$localStorage.counter) / 3600);
                    this.$localStorage.minutes = Math.floor(((this.duration - this.$localStorage.counter) - (this.$localStorage.hours * 3600))  / 60);
                    this.$localStorage.seconds = Math.floor((this.duration - this.$localStorage.counter) - ((this.$localStorage.hours * 3600) + (this.$localStorage.minutes * 60)));

                    // If timer reach the exercise duration
                    if (this.$localStorage.counter == this.duration) {
                        // Cancel timeout function
                        this.$timeout.cancel(onTimeout);

                        // Remove local storage
                        this.$localStorage.$reset();

                        // Execute registered callback
                        this.onEnd();
                    }
                }.bind(this);

                // Call for the first time the function to increase timer
                this.$timeout(onTimeout.bind(this), 1000);
            }
        ],
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