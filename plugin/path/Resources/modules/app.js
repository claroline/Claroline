/**
 * Path app
 */

import angular from 'angular/index'

export default class PathApp {
  // Resolve functions (it's the same between Editor and Player as we navigate in the same way in the 2 apps)
  static get resolveFunctions() {
    return {
      /**
       * Get the current Step from route params
       */
      step: [
        '$q',
        '$route',
        'PathService',
        function getCurrentStep($q, $route, PathService) {
          const deferred = $q.defer()

          // Retrieve the step from route ID
          let step = null
          if ($route.current.params && $route.current.params.stepId) {
            step = PathService.getStep($route.current.params.stepId)
          }

          if (angular.isDefined(step) && angular.isObject(step)) {
            deferred.resolve(step)
          } else {
            deferred.reject('step_not_found')
          }

          return deferred.promise
        }
      ],

      /**
       * Get inherited resources for the current Step
       */
      inheritedResources: [
        '$route',
        'PathService',
        function getCurrentInheritedResources($route, PathService) {
          let inherited = []

          const step = PathService.getStep($route.current.params.stepId)
          if (angular.isDefined(step) && angular.isObject(step)) {
            // Grab inherited resources
            inherited = PathService.getStepInheritedResources(step)
          }

          return inherited
        }
      ]
    }
  }

  // Get the Root step and its resources
  static get resolveRootFunctions() {
    return {
      /**
       * Get the Root step of the Path
       */
      step: [
        'PathService',
        function getRootStep(PathService) {
          return PathService.getRoot()
        }
      ],

      /**
       * Get inherited resources for the Root step
       */
      inheritedResources: [
        'PathService',
        function getRootInheritedResources(PathService) {
          let inherited = []

          const root = PathService.getRoot()
          if (angular.isObject(root)) {
            // Grab inherited resources
            inherited = PathService.getStepInheritedResources(root)
          }

          return inherited
        }
      ]
    }
  }

  /**
   * Run the angular application.
   *
   * @param $rootScope
   * @param $location
   * @param $anchorScroll
   */
  static run($rootScope, $location, $anchorScroll) {
    // Redirect to root step if the requested step is not found
    $rootScope.$on('$routeChangeError', (evt, current, previous, rejection) => {
      // If step not found, redirect user to rhe Root step
      if ('step_not_found' === rejection) {
        $location.path('/')
      }
    })

    // Automatically scroll to the Step content
    $rootScope.$on('$routeChangeSuccess', () => {
      $anchorScroll('scroll-to-onload')
    })
  }
}