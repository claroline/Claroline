/**
 * Path base controller
 *
 * @returns {PathBaseCtrl}
 * @constructor
 */
export default class PathBaseCtrl {
  constructor($window, $route, $routeParams, url, PathService) {
    this.window = $window
    this.UrlGenerator = url
    this.pathService = PathService

    // Store path to make it available by all UI components
    this.pathService.setPath(this.path)

    this.currentStep = $routeParams

    // Force reload of the route (as ng-view is deeper in the directive tree, route resolution is deferred and it causes issues)
    $route.reload()
  }
}
