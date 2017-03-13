/**
 * Step show controller
 */

import angular from 'angular/index'

import StepBaseCtrl from './StepBaseCtrl'

export default class StepShowCtrl extends StepBaseCtrl {
  constructor(step, inheritedResources, PathService, SummaryService, authorization, $sce, UserProgressionService) {
    super(step, inheritedResources, PathService, SummaryService)

    this.userProgressionService = UserProgressionService

    if (angular.isDefined(this.step) && angular.isDefined(this.step.description) && typeof this.step.description === 'string') {
      // Trust content to allow Cross Sites URL
      this.step.description = $sce.trustAsHtml(this.step.description)
    }

    this.authorization = authorization

    /**
     * Progression of the User for the current Step (NOT the progression for the whole Path)
     * @type {object}
     */
    this.progression = this.userProgressionService.getForStep(this.step)
    if (this.authorization && this.authorization.granted) {
      // User has access to the current step
      if (!angular.isObject(this.progression)) {
        // Create progression for User
        this.progression = this.userProgressionService.create(this.step, 'seen', true)
      } else {
        // Change the status when the user access the step
        const status = 'unseen' === this.progression.status  ? 'seen' : this.progression.status
        this.userProgressionService.update(this.step, status, true)
      }
    }
  }

  updateProgression(newStatus) {
    this.userProgressionService.update(this.step, newStatus)
  }
}
