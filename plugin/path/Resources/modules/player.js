/**
 * Path editor app
 */

import angular from 'angular/index'
import 'angular-route'
import 'angular-sanitize'
import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'angular-loading-bar'

import './authorization/module'
import './path/module'
import './summary/module'
import './navigation/module'
import './step/module'
import './user-progression/module'
import './condition/module'
import './appModuleBase'

import PathApp from './app'
import stepTemplate from './step/Partial/show.html'

angular
  // Path Player application
  .module('PathPlayerApp', [
    'ngSanitize',
    'ngRoute',
    'ui.bootstrap',
    'ui.translation',
    'PathModuleBase',
    'Authorization',
    'Path',
    'Summary',
    'Navigation',
    'Step',
    'UserProgression'
  ])

  // Declare routes
  .config([
    '$routeProvider',
    function PathPlayerConfig($routeProvider) {
      // Declare route to navigate between steps
      $routeProvider
        .when('/', {
          template: stepTemplate,
          controller: 'StepShowCtrl',
          controllerAs: 'stepShowCtrl',
          resolve: {
            // Always allow access to the Root step
            authorization: [
              'PathService',
              'AuthorizationCheckerService',
              function authorizationRootResolve(PathService, AuthorizationCheckerService) {
                return AuthorizationCheckerService.isAuthorized(PathService.getLastSeenStep())
              }
            ],
            step: [
              '$route',
              'PathService',
              function getLastSeenStep($route, PathService) {
                // Get the last seen step or the root if none
                const currentStep = PathService.getLastSeenStep()
                if (currentStep) {
                  $route.current.params.stepId = currentStep.id
                }

                return currentStep
              }
            ],
            inheritedResources: [
              'PathService',
              function getLastSeenInheritedResources(PathService) {
                let inherited = []

                const currentStep = PathService.getLastSeenStep()
                if (angular.isObject(currentStep)) {
                  // Grab inherited resources
                  inherited = PathService.getStepInheritedResources(currentStep)
                }

                return inherited
              }
            ]
          }
        })
        .when('/:stepId?', {
          template: stepTemplate,
          controller: 'StepShowCtrl',
          controllerAs: 'stepShowCtrl',
          resolve: angular.merge({
            // Add authorization checker
            authorization: [
              '$route',
              'PathService',
              'AuthorizationCheckerService',
              function authorizationResolve($route, PathService, AuthorizationCheckerService) {
                return AuthorizationCheckerService.isAuthorized(PathService.getStep($route.current.params.stepId))
              }
            ]
          }, PathApp.resolveFunctions)
        })
        .otherwise({
          redirectTo: '/:stepId?'
        })
    }
  ])

  // Bind run function
  .run([
    '$rootScope',
    '$location',
    '$anchorScroll',
    PathApp.run
  ])