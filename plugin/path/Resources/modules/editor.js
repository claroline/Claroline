/**
 * Path editor app
 */

import angular from 'angular/index'
import 'angular-route'
import 'angular-sanitize'
import 'angular-ui-bootstrap'
import '#/main/core/innova/angular-translation'

import './path/module'
import './summary/module'
import './navigation/module'
import './step/module'
import './user-progression/module'
import './condition/module'
import './appModuleBase'

import PathApp from './app'
import stepTemplate from './step/Partial/edit.html'

angular
  .module('PathEditorApp', [
    'ngSanitize',
    'ngRoute',
    'ui.bootstrap',
    'ui.translation',
    'PathModuleBase',
    'Path',
    'Summary',
    'Navigation',
    'Step',
    'UserProgression'
  ])

  // Declare routes
  .config([
    '$routeProvider',
    function PathEditorConfig($routeProvider) {
      $routeProvider
        .when('/', {
          template: stepTemplate,
          controller: 'StepEditCtrl',
          controllerAs: 'stepEditCtrl',
          resolve: PathApp.resolveRootFunctions
        })
        .when('/:stepId?', {
          template: stepTemplate,
          controller: 'StepEditCtrl',
          controllerAs: 'stepEditCtrl',
          resolve: PathApp.resolveFunctions
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
