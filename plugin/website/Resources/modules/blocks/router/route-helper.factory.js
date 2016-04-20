/**
 * Created by ptsavdar on 15/03/16.
 */
import angular from 'angular/index'

let _$route = new WeakMap()
let _routeHelperConfig = new WeakMap()
let _$routeProvider = new WeakMap()


export default class RouteHelper {
  constructor ($location, $rootScope, $route, routeHelperConfig) {
    _$route.set(this, $route)
    _routeHelperConfig.set(this, routeHelperConfig)
    _$routeProvider.set(this, routeHelperConfig.config.$routeProvider)

    this._routeChangeError($rootScope, $location, routeHelperConfig)
    this._routeChangeSuccess($rootScope, routeHelperConfig)
  }

  getRoutes() {
    let routes = []
    for (var prop in _$route.get(this).routes) {
      if (_$route.get(this).routes.hasOwnProperty(prop)) {
        var route = _$route.get(this).routes[ prop ]
        var isRoute = !!route.option
        if (isRoute) {
          routes.push(route)
        }
      }
    }

    return routes
  }

  configureRoutes(routes) {
    routes.forEach(
      (route) => {
        route.config.resolve =
          angular.extend(route.config.resolve || {}, _routeHelperConfig.get(this).config.resolveAlways)
        _$routeProvider.get(this).when(route.url, route.config)
      }
    )
    _$routeProvider.get(this).otherwise({redirectTo: '/structure'})
  }

  _routeChangeError($rootScope, $location, routeHelperConfig) {
    $rootScope.$on('$routeChangeError', () => {$location.path(routeHelperConfig.config.defaultPath)})
  }

  _routeChangeSuccess($rootScope, routeHelperConfig) {
    $rootScope.$on('$routeChangeSuccess', (event, current) => {routeHelperConfig.config.rootChangeSuccess($rootScope, current)})
  }
}

RouteHelper.$inject = [ '$location', '$rootScope', '$route', 'routeHelperConfig' ]
