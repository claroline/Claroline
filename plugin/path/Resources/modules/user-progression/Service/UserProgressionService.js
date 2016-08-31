/**
 * User Progression Service
 */

import angular from 'angular/index'

export default class UserProgressionService {
  constructor($http, $q, Translator, url, AlertService) {
    this.$http = $http
    this.$q = $q
    this.Translator = Translator
    this.UrlGenerator = url
    this.AlertService = AlertService

    /**
     * Progression of the current User
     * @type {object}
     */
    this.progression = null

    /**
     * Current user's total progression for the current path
     * @type {number}
     */
    this.totalProgression = 0

    /**
     * Progression in progress of been updated or created
     * @type {object}
     */
    this.inProgress = {}
  }

  /**
   * Get User progression for the current Path
   *
   * @returns {Object}
   */
  get() {
    return this.progression
  }

  /**
   * Set User progression for the current Path
   *
   *  @param value
   */
  set(value) {
    this.progression = value
  }

  /**
   * Get the User progression for the specified Step
   *
   * @param step
   *
   * @returns {Object|null}
   */
  getForStep(step) {
    let stepProgression = null
    if (angular.isObject(this.progression) && angular.isObject(this.progression[step.resourceId])) {
      stepProgression = this.progression[step.resourceId]
    }

    return stepProgression
  }

  isStepInProgress(step) {
    return this.inProgress[step.resourceId] || false
  }

  /**
   * Check if a step has already been marked as authorized
   * @param step
   */
  iStepAlreadyAuthorized(step) {
    let alreadyAuthorized = false

    const stepProgression = this.getForStep(step)
    if (stepProgression && stepProgression.authorized) {
      // Step is already authorized
      alreadyAuthorized = true
    }

    return alreadyAuthorized
  }

  /**
   * Create a new Progression for the Step
   *
   * @param step
   * @param [status]
   * @param authorized
   *
   * @returns {object}
   */
  create(step, status, authorized) {
    const deferred = this.$q.defer()

    // If step is already in update progress or create progress then do nothing
    if (this.isStepInProgress(step)) {
      return deferred.promise
    }

    const params = {
      user_progression_authorized: authorized ? true : false,
      user_progression_status: status ? status : 'unseen'
    }

    this.inProgress[step.resourceId] = true

    this.$http
      .post(this.UrlGenerator('innova_path_progression_create', { id: step.resourceId }), params)
      .success((response) => {
        // Store step progression in the Path progression array
        this.inProgress[step.resourceId] = false
        this.progression[response.progression.stepId] = response.progression
        if (response.progression.status === 'seen' || response.progression.status === 'done') {
          this.totalProgression += 1
        }

        deferred.resolve(response)
      })
      .error((response) => {
        this.inProgress[step.resourceId] = false
        this.AlertService.addAlert('error', this.Translator.trans('progression_save_error', {}, 'path_wizards'))

        deferred.reject(response)
      })

    return deferred.promise
  }

  /**
   * Update Progression of the User for a Step
   *
   * @param step
   * @param status
   * @param authorized
   */
  update(step, status, authorized) {
    const deferred = this.$q.defer()

    const params = {
      user_progression_authorized: authorized || this.progression[step.id].authorized,
      user_progression_status: status || this.progression[step.id].status
    }

    this.inProgress[step.resourceId] = true

    this.$http
      .put(this.UrlGenerator('innova_path_progression_update', { id: step.resourceId }), params)

      .success((response) => {
        this.inProgress[step.resourceId] = false

        // Store step progression in the Path progression array
        let oldStatus = ''
        if (!angular.isObject(this.progression[response.progression.stepId])) {
          this.progression[response.progression.stepId] = response.progression
        } else {
          oldStatus = this.progression[response.progression.stepId].status
          this.progression[response.progression.stepId].status = response.progression.status
          this.progression[response.progression.stepId].authorized = response.progression.authorized
        }
        if ((status === 'seen' || status === 'done') && oldStatus !== 'seen' && oldStatus !== 'done') {
          this.totalProgression += 1
        } else if ((oldStatus === 'seen' || oldStatus === 'done') && status !== 'seen' && status !== 'done') {
          this.totalProgression -= 1
        }
        deferred.resolve(response.progression.status)
      })

      .error((response) => {
        this.inProgress[step.resourceId] = false
        this.AlertService.addAlert('error', this.Translator.trans('progression_save_error', {}, 'path_wizards'))

        deferred.reject(response)
      })

    return deferred.promise
  }

  /**
   * Get user's total progression for current path
   *
   * @returns {number}
   */
  getTotalProgression() {
    return this.totalProgression
  }

  /**
   * Set user's total progression for current path
   *
   * @param value
   */
  setTotalProgression(value) {
    this.totalProgression = parseInt(value)
  }

  /**
   * call for unlock step : call Controller method that triggers log listener and notification
   */
  callForUnlock(step) {
    const deferred = this.$q.defer()

    this.$http
        .get(this.UrlGenerator('innova_path_step_callforunlock', {step: step.resourceId}))
        // returns a progression object
        .success((response) => {
          //update progression
          if (!angular.isObject(this.progression[step.stepId])) {
            this.progression[response.stepId] = response
          } else {
            this.progression[response.stepId].lockedcall = response.lockedcall
          }

          deferred.resolve(response)
        })
        .error((response) => {
          deferred.reject(response)
        })

    return deferred.promise
  }
}
