/**
 * Authorization module
 * Manages step access authorizations
 */

import angular from 'angular/index'

import './../path/module'
import './../step/module'
import './../user-progression/module'
import './../condition/module'

import AuthorizationCheckerService from './Service/AuthorizationCheckerService'
import AuthorizationBlockDirective from './Directive/AuthorizationBlockDirective'

angular
  .module('Authorization', [
    'Path',
    'Step',
    'UserProgression',
    'Condition'
  ])
  .service('AuthorizationCheckerService', [
    '$q',
    'PathService',
    'StepService',
    'UserProgressionService',
    'StepConditionsService',
    AuthorizationCheckerService
  ])
  .directive('authorizationBlock', [
    () => new AuthorizationBlockDirective
  ])
