import angular from 'angular/index'

import 'angular-route'
import 'angular-loading-bar'
import 'angular-strap'
import 'angular-ui-translation/angular-translation'
import '#/main/core/fos-js-router/module'
import '#/main/core/workspace/module'
import '#/main/core/authentication/module'
import '#/main/core/translation/module'

import './dashboards/module'
import './dashboard/module'
import './admin/module'

import dashboards from './dashboards/Partials/dashboards.html'
import admin from './admin/Partials/admin.html'
import dashboard from './dashboard/Partials/dashboard.html'

angular
  // Declare the new Application
    .module('DashboardApp', [
      'ngRoute',
      'angular-loading-bar',
      'ui.fos-js-router',
      'ui.translation',
      'mgcrea.ngStrap.datepicker',
      'authentication',
      'translation',
      'workspace',
      'Dashboards',
      'Admin',
      'Dashboard'
    ])
    // Configure application
    .config([
      '$routeProvider',
      'cfpLoadingBarProvider',
      '$datepickerProvider',
      function DashboardAppConfig($routeProvider, cfpLoadingBarProvider, $datepickerProvider) {
        // Configure loader
        cfpLoadingBarProvider.latencyThreshold = 200
        cfpLoadingBarProvider.includeBar       = false
        cfpLoadingBarProvider.spinnerTemplate  = '<div class="loading">Loading&#8230;</div>'

        // Configure DatePicker
        angular.extend($datepickerProvider.defaults, {
          dateFormat: 'dd/MM/yyyy',
          dateType: 'string',
          startWeek: 1,
          iconLeft: 'fa fa-fw fa-chevron-left',
          iconRight: 'fa fa-fw fa-chevron-right',
          modelDateFormat: 'yyyy-MM-dd\THH:mm:ss',
          autoclose: true
        })

        // Define routes
        $routeProvider
          // Dahsboards list
          .when('/', {
            template: dashboards,
            controller  : 'DashboardsCtrl',
            controllerAs: 'dashboardsCtrl',
            resolve: {
              user:[
                'UserService',
                function userResolve(UserService) {
                  return UserService.getConnectedUser()
                }
              ],
              dashboards: [
                'DashboardService',
                function dashboardsResolve(DashboardService) {
                  return DashboardService.getAll()
                }
              ]
            }
          })
          .when('/dashboards/:id', {
            template: dashboard,
            controller  : 'DashboardCtrl',
            controllerAs: 'dashboardCtrl',
            resolve: {
              user:[
                'UserService',
                function userResolve(UserService) {
                  return UserService.getConnectedUser()
                }
              ],
              dashboard: [
                '$route',
                'DashboardService',
                function dashboardResolve($route, DashboardService) {
                  var promise = null
                  if ($route.current.params && $route.current.params.id) {
                    promise = DashboardService.getOne($route.current.params.id)
                  }

                  return promise
                }
              ],
              data:[
                '$route',
                'DashboardService',
                function dashboardResolve($route, DashboardService) {
                  var promise = null
                  if ($route.current.params && $route.current.params.id) {
                    promise = DashboardService.getDashboardData($route.current.params.id)
                  }

                  return promise
                }
              ]
            }
          })
          .when('/new', {
            template: admin,
            controller  : 'AdminDashboardCtrl',
            controllerAs: 'adminDashboardCtrl',
            resolve: {
              user:[
                'UserService',
                function userResolve(UserService) {
                  return UserService.getConnectedUser()
                }
              ],
              workspaces: [
                'WorkspaceService',
                function workspacesResolve(WorkspaceService) {
                  return WorkspaceService.getConnectedUserWorkspaces()
                }
              ],
              nbDashboards:[
                'DashboardService',
                function nbDashboardResolve(DashboardService) {
                  return DashboardService.countDashboards()
                }
              ],
              dashboard: [
                function dashboardResolve() {
                  const today = new Date()
                  const dashboardDefaultName = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate()
                  return {
                    name: dashboardDefaultName
                  }
                }
              ]
            }
          })
          .when('/dashboards/:id/edit', {
            template: admin,
            controller  : 'AdminDashboardCtrl',
            controllerAs: 'adminDashboardCtrl',
            resolve: {
              user:[
                'UserService',
                function userResolve(UserService) {
                  return UserService.getConnectedUser()
                }
              ],
              workspaces: [
                'WorkspaceService',
                function workspacesResolve(WorkspaceService) {
                  return WorkspaceService.getConnectedUserWorkspaces()
                }
              ],
              nbDashboards:[
                'DashboardService',
                function nbDashboardResolve(DashboardService) {
                  return DashboardService.countDashboards()
                }
              ],
              dashboard: [
                '$route',
                'DashboardService',
                function dashboardResolve($route, DashboardService) {
                  var promise = null
                  if ($route.current.params && $route.current.params.id) {
                    promise = DashboardService.getOne($route.current.params.id)
                  }

                  return promise
                }
              ]
            }
          })
          // Otherwise redirect User on Overview
          .otherwise({
            redirectTo: '/'
          })
      }
    ])
