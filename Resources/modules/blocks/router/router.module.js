import angular from 'angular/index'
import routeHelperProvider from './route-helper.provider'
import routeHelperFactory from './route-helper.factory'

angular
  .module('blocks.router', [
    'ngRoute'
  ])
  .provider('routeHelperConfig', routeHelperProvider)
  .factory('routeHelper', routeHelperFactory)
