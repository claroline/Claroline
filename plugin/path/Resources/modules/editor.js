/**
 * Path editor app
 */

import angular from 'angular/index'
import 'angular-route'
import 'angular-sanitize'
import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'

import './path/module'
import './summary/module'
import './navigation/module'
import './step/module'
import './user-progression/module'
import './condition/module'

import PathApp from './app'
import stepTemplate from './step/Partial/edit.html'

const pathApp = new PathApp()

angular
  .module('PathEditorApp', [
    'ngSanitize',
    'ngRoute',
    'ui.bootstrap',
    'ui.translation',

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
          resolve: pathApp.resolveRootFunctions
        })
        .when('/:stepId?', {
          template: stepTemplate,
          controller: 'StepEditCtrl',
          controllerAs: 'stepEditCtrl',
          resolve: pathApp.resolveFunctions
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
    pathApp.run
  ])
