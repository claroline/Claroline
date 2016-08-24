/**
 * Condition module
 * Manages user access inside the Path
 */

import angular from 'angular/index'

import './../confirm/module'
import './../form/module'
import './../criteria-group/module'

import StepConditionsService from './Service/StepConditionsService'
import ConditionEditCtrl from './Controller/ConditionEditCtrl'
import ConditionEditDirective from './Directive/ConditionEditDirective'

angular
  .module('Condition', [
    'Confirm',
    'Form',
    'CriteriaGroup'
  ])
  .service('StepConditionsService', [
    '$q',
    'CriteriaGroupService',
    StepConditionsService
  ])
  .controller('ConditionEditCtrl', [
    'ConfirmService',
    'StepConditionsService',
    ConditionEditCtrl
  ])
  .directive('conditionEdit', [
    () => new ConditionEditDirective
  ])
