/**
 * AuthorizationChecker Service
 */

import angular from 'angular/index'

export default class AuthorizationCheckerService {
  /**
   *
   * @param $q
   * @param {PathService} PathService
   * @param {StepService} StepService
   * @param {UserProgressionService} UserProgressionService
   * @param {StepConditionsService} StepConditionsService
   */
  constructor($q, Translator, PathService, StepService, UserProgressionService, StepConditionsService) {
    this.$q = $q
    this.Translator = Translator
    this.PathService = PathService
    this.StepService = StepService
    this.UserProgressionService = UserProgressionService
    this.ConditionService = StepConditionsService

    this.editEnabled = false
  }

  isEditEnabled() {
    return this.editEnabled
  }

  setEditEnabled(value) {
    this.editEnabled = value
  }

  /**
   * Check if the User can access to the Step.
   *
   * @param step
   */
  isAuthorized(step) {
    const deferred = this.$q.defer()

    if (!angular.isObject(step)) {
      deferred.resolve({
        granted: true,
        errors: []
      })
    } else if (this.isEditEnabled() || 0 === step.lvl) {
      // Bypass check if the user is admin or the step is the root of the path
      deferred.resolve({
        granted: true,
        errors: []
      })
    } else if (!this.StepService.isBetweenAccessibilityDates(step)) {
      // No access to step that are not in accessibility dates
      deferred.resolve({
        granted: false,
        errors: [this.Translator.trans('step_not_accessible', {}, 'path_wizards')]
      })
    } else if (this.UserProgressionService.iStepAlreadyAuthorized(step)) {
      // User has already unlock the step
      deferred.resolve({
        granted: true,
        errors: []
      })
    } else {
      const conditionChecks = []
      const currentAuth = {
        granted: true,
        errors: []
      }

      // Check if the previous step condition is met
      const previous = this.PathService.getPrevious(step)
      if (previous.condition) {
        // Check condition of the previous step
        conditionChecks.push(
          this.ConditionService
            .testCondition(previous)
            .then(errors => {
              if (0 !== errors.length) {
                // Step is not accessible
                currentAuth.granted = false
                currentAuth.errors = currentAuth.errors.concat(errors)
              }
            })
        )
      }

      if (this.PathService.getPath().completeBlockingCondition) {
        // All the previous steps need to be unlocked to access
        conditionChecks.push(
          this.isAuthorized(previous).then(authorization => {
            currentAuth.granted = currentAuth.granted && authorization.granted
            currentAuth.errors = currentAuth.errors.concat(authorization.errors)
          })
        )
      }

      this.$q
        .all(conditionChecks)
        .then(() => {
          deferred.resolve(currentAuth)
        })
    }

    return deferred.promise
  }
}
