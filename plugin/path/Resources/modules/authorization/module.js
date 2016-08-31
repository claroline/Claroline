/**
 * Authorization module
 * Manages step access authorizations
 */

import angular from 'angular/index'

import '#/main/core/translation/module'
import './../path/module'
import './../step/module'
import './../user-progression/module'
import './../condition/module'

import AuthorizationCheckerService from './Service/AuthorizationCheckerService'
import AuthorizationBlockDirective from './Directive/AuthorizationBlockDirective'

angular
  .module('Authorization', [
    'translation',
    'Path',
    'Step',
    'UserProgression',
    'Condition'
  ])
  .service('AuthorizationCheckerService', [
    '$q',
    'Translator',
    'PathService',
    'StepService',
    'UserProgressionService',
    'StepConditionsService',
    AuthorizationCheckerService
  ])
  .directive('authorizationBlock', [
    () => new AuthorizationBlockDirective
  ])
