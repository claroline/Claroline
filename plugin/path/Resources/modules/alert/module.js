/**
 * Alert module
 */

import angular from 'angular/index'

import AlertService from './Service/AlertService'
import AlertBoxDirective from './Directive/AlertBoxDirective'

angular
  .module('Alert', [])

  .service('AlertService', [
    '$timeout',
    AlertService
  ])
  .directive('alertBox', [
    'AlertService',
    (AlertService) => new AlertBoxDirective(AlertService)
  ])
