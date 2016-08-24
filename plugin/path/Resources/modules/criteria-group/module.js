/**
 * Criteria group module
 */

import angular from 'angular/index'

import './../confirm/module'
import './../criterion/module'

import CriteriaGroupService from './Service/CriteriaGroupService'
import CriteriaGroupCtrl from './Controller/CriteriaGroupCtrl'
import CriteriaGroupDirective from './Directive/CriteriaGroupDirective'

angular
  .module('CriteriaGroup', [
    'Confirm',
    'Criterion'
  ])
  .service('CriteriaGroupService', [
    '$q',
    'CriterionService',
    CriteriaGroupService
  ])
  .controller('CriteriaGroupCtrl', [
    'ConfirmService',
    'CriteriaGroupService',
    'CriterionService',
    CriteriaGroupCtrl
  ])
  .directive('criteriaGroup', [
    () => new CriteriaGroupDirective
  ])
