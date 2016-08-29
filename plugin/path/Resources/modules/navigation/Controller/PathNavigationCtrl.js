/**
 * Path navigation controller
 */

import angular from 'angular/index'

export default class PathNavigationCtrl {
  constructor($routeParams, $scope, PathService) {
    this.pathService = PathService

    /**
     * Current matched route
     * @type {object}
     */
    this.current = $routeParams

    /**
     * Current displayed step
     * @type {object}
     */
    this.step = {}

    /**
     * Parents of the current step
     * @type {object}
     */
    this.parents = {}

    // Watch the route changes
    $scope.$watch(() => this.current, this.reloadStep.bind(this), true)

    // Watch the step property
    $scope.$watch(() => this.step, this.reloadStep.bind(this), true)
  }

  /**
   * Reload the Step from route params
   */
  reloadStep() {
    this.step = null

    // Get step
    if (angular.isDefined(this.current) && angular.isDefined(this.current.stepId)) {
      // Retrieve current step
      this.step = this.pathService.getStep(this.current.stepId)
    } else {
      // Get the root
      this.step = this.pathService.getRoot()
    }

    // Get parents of the step
    if (angular.isDefined(this.step) && angular.isObject(this.step)) {
      this.parents = this.pathService.getParents(this.step)
    }
  }
}
