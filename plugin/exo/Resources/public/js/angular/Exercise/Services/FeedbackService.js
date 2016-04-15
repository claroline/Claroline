/**
 * Feedback Service
 * Manages feedback for Exercises
 * @constructor
 */
var FeedbackService = function FeedbackService() {

};

// Set up dependency injection
FeedbackService.$inject = [];

/**
 * Feedback configuration
 * @type {Object}
 */
FeedbackService.prototype.config = {
    /**
     * Is feedback available for the current Exercise ?
     * @type {boolean}
     */
    enabled: false,

    /**
     * Is feedback currently displayed ?
     * @type {boolean}
     */
    visible: false
};

/**
 * Callbacks to execute when the visibility state of Feedback changes
 * @type {Array}
 */
FeedbackService.prototype.callbacks = {
    /**
     * Callbacks to execute when the Feedback are shown
     * @type {Array}
     */
    show: [],

    /**
     * Callbacks to execute when the Feedback are hidden
     * @type {Array}
     */
    hide: []
};

/**
 * Get feedback information
 * @return {Object}
 */
FeedbackService.prototype.get = function get() {
    return this.config;
};

/**
 * Is feedback enabled ?
 * @returns {boolean}
 */
FeedbackService.prototype.isEnabled = function isEnabled() {
    return this.config.enabled;
};

/**
 * Enable feedback for the current Exercise
 * @returns {FeedbackService}
 */
FeedbackService.prototype.enable = function enableFeedback() {
    this.config.enabled = true;

    return this;
};

/**
 * Disable feedback for the current Exercise
 * @returns {FeedbackService}
 */
FeedbackService.prototype.disable = function disableFeedback() {
    this.config.enabled = false;

    return this;
};

/**
 * Is feedback displayed ?
 * @returns {boolean}
 */
FeedbackService.prototype.isVisible = function isVisible() {
    return this.config.visible;
};

/**
 * Display feedback
 * @returns {FeedbackService}
 */
FeedbackService.prototype.show = function showFeedback() {
    this.config.visible = true;

    // Execute callbacks
    this.executeCallbacks('show');

    return this;
};

/**
 * Hide feedback
 * @returns {FeedbackService}
 */
FeedbackService.prototype.hide = function hideFeedback() {
    this.config.visible = false;

    // Execute callbacks
    this.executeCallbacks('hide');

    return this;
};

/**
 * Execute all callbacks for a given event
 * @param   {string} event
 * @returns {FeedbackService}
 */
FeedbackService.prototype.executeCallbacks = function executeCallbacks(event) {
    if (this.callbacks[event]) {
        for (var i = 0; i < this.callbacks[event].length; i++) {
            var callback = this.callbacks[event][i];

            // Execute callback
            callback();
        }
    }

    return this;
};

/**
 * Register to a Feedback event
 * @param   {String}   event
 * @param   {Function} callback
 * @returns {FeedbackService}
 */
FeedbackService.prototype.on = function on(event, callback) {
    if (!this.callbacks[event]) {
        // Event is not managed
        console.error('FeedbackService.on(event, callback) : Try to register to an undefined event (events = ' + Object.keys(this.callbacks) + ').');
    }

    if (typeof callback !== 'function') {
        // Callback is not a function
        console.error('FeedbackService.on(event, callback) : Parameter `callback` must be a Function.');
    }

    // Register callback
    this.callbacks[event].push(callback);

    return this;
};

/**
 * Reset feedback (hide and remove registered callbacks)
 * @returns {FeedbackService}
 */
FeedbackService.prototype.reset = function reset() {
    this.hide();

    // Remove events
    for (var event in this.callbacks) {
        if (this.callbacks.hasOwnProperty(event)) {
            this.callbacks[event].splice(0, this.callbacks[event].length);
        }
    }

    return this;
};

// Register service into AngularJS
angular
    .module('Exercise')
    .service('FeedbackService', FeedbackService);