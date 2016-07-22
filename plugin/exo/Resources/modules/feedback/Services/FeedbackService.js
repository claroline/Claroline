/**
 * Feedback Service
 * Manages feedback for Exercises
 * @constructor
 */
export default class FeedbackService {
  /**
   * Class constructor
   * @param {object} $log
   */
  constructor($log) {
    this.SOLUTION_FOUND           = 0
    this.ONE_ANSWER_MISSING       = 1
    this.MULTIPLE_ANSWERS_MISSING = 2

    // Get angular logger
    this.$log = $log

    /**
     * Feedback configuration
     * @type {Object}
     */
    this.config = {
      /**
       * Is feedback available for the current Exercise ?
       * @type {boolean}
       */
      enabled: false,

      /**
       * Is feedback currently displayed ?
       * @type {boolean}
       */
      visible: false,

      /**
       * The state of the feedback for each question
       * @type {Object}
       */
      state: {}
    }

    /**
     * Callbacks to execute when the visibility state of Feedback changes
     * @type {Array}
     */
    this.callbacks = {
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
    }
  }

  /**
   * Get feedback information
   * @return {Object}
   */
  get() {
    return this.config
  }

  /**
   * Is feedback enabled ?
   * @returns {boolean}
   */
  isEnabled() {
    return this.config.enabled
  }

  /**
   * Enable feedback for the current Exercise
   * @returns {FeedbackService}
   */
  enable() {
    this.config.enabled = true

    return this
  }

  /**
   * Disable feedback for the current Exercise
   * @returns {FeedbackService}
   */
  disable() {
    this.config.enabled = false

    return this
  }

  /**
   * Is feedback displayed ?
   * @returns {boolean}
   */
  isVisible() {
    return this.config.visible
  }

  /**
   * Display feedback
   * @returns {FeedbackService}
   */
  show() {
    this.config.visible = true

    // Execute callbacks
    this.executeCallbacks('show')

    return this
  }

  /**
   * Hide feedback
   * @returns {FeedbackService}
   */
  hide() {
    this.config.visible = false

    // Execute callbacks
    this.executeCallbacks('hide')

    return this
  }

  /**
   * Execute all callbacks for a given event
   * @param   {string} event
   * @returns {FeedbackService}
   */
  executeCallbacks(event) {
    if (this.callbacks[event]) {
      for (let i = 0; i < this.callbacks[event].length; i++) {
        const callback = this.callbacks[event][i]

        // Execute callback
        callback()
      }
    }

    return this
  }

  /**
   * Register to a Feedback event
   * @param   {String}   event
   * @param   {Function} callback
   * @returns {FeedbackService}
   */
  on(event, callback) {
    if (!this.callbacks[event]) {
      // Event is not managed
      this.$log.error('FeedbackService.on(event, callback) : Try to register to an undefined event (events = ' + Object.keys(this.callbacks) + ').')
    }

    if (typeof callback !== 'function') {
      // Callback is not a function
      this.$log.error('FeedbackService.on(event, callback) : Parameter `callback` must be a Function.')
    }

    // Register callback
    this.callbacks[event].push(callback)

    return this
  }

  /**
   * Reset feedback (hide and remove registered callbacks)
   * @returns {FeedbackService}
   */
  reset() {
    if (this.isVisible()) {
      this.hide()
    }

    // Remove events
    for (let event in this.callbacks) {
      if (this.callbacks.hasOwnProperty(event)) {
        this.callbacks[event].splice(0, this.callbacks[event].length)
      }
    }

    return this
  }
}
