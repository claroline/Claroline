var TimerService = function TimerService($timeout, $localStorage) {
    this.$timeout      = $timeout;
    this.$localStorage = $localStorage;

    // Add Timer wrapper in localStorage (to keep things organized if other parts of the APP use localStorage)
    this.$localStorage.$default({
        timers: {}
    });
};

// Set up dependency injection
TimerService.$inject = [ '$timeout', '$localStorage' ];

/**
 * Get the Timer identified by `id`
 * @param   {string}  id the identifier of the Timer
 * @returns {Object|null} the Timer definition or null if the timer does not exist
 */
TimerService.prototype.get = function get(id) {
    var timer = null;
    if (this.$localStorage.timers[id]) {
        timer = this.$localStorage.timers[id];
    }

    return timer;
};

/**
 * Create a new Timer identified by `id`
 * @param   {string}  id           the identifier of the Timer
 * @param   {number}  duration     the duration of the timer
 * @param   {function} endCallback a function to execute when the Timer reaches the end
 * @param   {boolean} [autoStart]  if true the timer is started directly after creation
 * @returns {Object} the created Timer definition
 */
TimerService.prototype.new = function newTimer(id, duration, endCallback, autoStart) {
    var timeout = this.$timeout;

    if (!this.$localStorage.timers[id]) {
        // Create and store timer definition in localStorage
        this.$localStorage.timers[id] = {
            /**
             * Is the Timer currently running ?
             * @type {boolean}
             */
            started: false,

            /**
             * The identifier of the Timer
             * @type {string}
             */
            id: id,

            /**
             * The total duration of the Timer (in seconds for easier calculations)
             * @type {number}
             */
            duration: duration,

            /**
             * Elapsed time since the timer is started
             * @type {number}
             */
            elapsed: 0,

            /**
             * Remaining time information
             * @type {object}
             */
            remaining: {
                hours: 0,
                minutes: 0,
                seconds: 0
            },

            /**
             * The callback to execute when the Timer is finished
             * @type {function}
             */
            end: endCallback,

            /**
             * Run the timer
             * Increases elapsed time ans decreases remaining
             */
            run: function run() {
                // Increase elapsed time
                this.elapsed++;

                // Recalculate remaining time
                var remaining = this.duration - this.elapsed;

                var hoursMod = remaining % 3600; // Hours which are not plain
                this.remaining.hours = (remaining - hoursMod) / 3600;

                var minutesMod = hoursMod % 60; // Minutes which are not plain
                this.remaining.minutes = (hoursMod - minutesMod) / 60;

                this.remaining.seconds = minutesMod;

                // Rerun or end if the Timer has ended
                if (this.elapsed === this.duration) {
                    // The timer is ended
                    this.end();
                } else {
                    // Continue
                    this.timeout = timeout(this.run.bind(this), 1000);
                }
            },

            /**
             * The timeout promise while the Timer is running
             * @type {Object|null}
             */
            timeout: null
        };
    }

    if (autoStart) {
        this.start(id);
    }

    return this.$localStorage.timers[id];
};

/**
 * Convert time in seconds in hours, minutes, seconds
 * @param {number} time
 * @return {{
 *      hours: number,
 *      minutes: number,
 *      seconds: number
 * }}
 */
TimerService.prototype.convertTime = function extractTimeParts(time) {

};

/**
 * Destroy the Timer identified by `id`
 * @param {string}  id the identifier of the Timer
 */
TimerService.prototype.start = function start(id) {
    if (this.$localStorage.timers[id]) {
        if (!this.$localStorage.timers[id].started) {
            this.$timeout(this.$localStorage.timers[id].run.bind(this.$localStorage.timers[id]), 1000);

            // Mark the Timer as started
            this.$localStorage.timers[id].started = true;
        }
    } else {
        // Call to an undefined timer
        console.error('TimerService : the Timer referenced by "' + id + '" does not exist. Unable to process start.');
    }
};

/**
 * Stop the Timer identified by `id`
 * A call to `.start()` will restart the Timer where it was stopped
 * @param {string}  id the identifier of the Timer
 */
TimerService.prototype.stop = function stop(id) {
    if (this.$localStorage.timers[id] && this.$localStorage.timers[id].started) {
        // Clear timeout
        this.$timeout.cancel(this.$localStorage.timers[id].timeout);

        // Mark the Timer as stopped
        this.$localStorage.timers[id].started = false;
    }
};

/**
 * Destroy the Timer identified by `id`
 * @param {string}  id the identifier of the Timer
 */
TimerService.prototype.destroy = function destroy(id) {
    if (this.$localStorage.timers[id]) {
        // Clear timeout
        this.stop(id);

        // Delete timer from localStorage
        delete this.$localStorage.timers[id];
    }
};

// Register service into AngularJS
angular
    .module('Common')
    .service('TimerService', TimerService);