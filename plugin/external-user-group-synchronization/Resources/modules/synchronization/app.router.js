/**
 * Created by panos on 5/30/17.
 */
import { states } from './app.routing'

export function syncRouterConfig($stateProvider, $urlRouterProvider) {
  return new SyncRouter($stateProvider, $urlRouterProvider)
}

syncRouterConfig.$inject = ['$stateProvider', '$urlRouterProvider']

class SyncRouter {
  constructor($stateProvider, $urlRouterProvider) {
    this._$stateProvider = $stateProvider
    this._$urlRouterProvider = $urlRouterProvider
    this.$onInit()
  }

  $onInit() {
    states.forEach(state => {
      this._$stateProvider.state(state)
    })
    this._$urlRouterProvider.otherwise('/users')
  }
}