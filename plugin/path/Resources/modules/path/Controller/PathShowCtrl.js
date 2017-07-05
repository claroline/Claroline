/**
 * Path show
 */

import angular from 'angular/index'

import PathBaseCtrl from './PathBaseCtrl'

export default class PathShowCtrl extends PathBaseCtrl {
  /**
   *
   * @param $window
   * @param $route
   * @param $routeParams
   * @param {PathService} PathService
   * @param {AuthorizationCheckerService} AuthorizationCheckerService
   * @param {UserProgressionService} UserProgressionService
   */
  constructor($window, $route, $routeParams, url, PathService, AuthorizationCheckerService, UserProgressionService) {
    super($window, $route, $routeParams, url, PathService)

    this.AuthorizationCheckerService = AuthorizationCheckerService

    /**
     * Progression of the current User (key => stepId, value => json representation of UserProgression Entity)
     * @type {UserProgressionService}
     */
    this.userProgressionService = UserProgressionService

    // Store UserProgression
    if (angular.isObject(this.userProgression)) {
      this.userProgressionService.set(this.userProgression)
    }

    this.AuthorizationCheckerService.setEditEnabled(this.editEnabled)
    this.userProgressionService.setTotalProgression(this.totalProgression)
  }

  /**
   * Open Path editor
   */
  edit() {
    let url = this.UrlGenerator('innova_path_editor_wizard', {
      id: this.path.id
    })

    if (angular.isObject(this.currentStep) && angular.isDefined(this.currentStep.stepId)) {
      url += '#/' + this.currentStep.stepId
    }

    this.window.location.href = url
  }

  getTotalProgression() {
    const total = Math.round((this.userProgressionService.getTotalProgression() * 100) / this.pathService.getTotalSteps())

    return total || 0
  }
}
