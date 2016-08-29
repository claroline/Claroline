/**
 * Criteria group module
 */

import angular from 'angular/index'

import '#/main/core/translation/module'
import './../confirm/module'
import './../criterion/module'

import CriteriaGroupService from './Service/CriteriaGroupService'
import CriteriaGroupCtrl from './Controller/CriteriaGroupCtrl'
import CriteriaGroupDirective from './Directive/CriteriaGroupDirective'

angular
  .module('CriteriaGroup', [
    'translation',
    'Confirm',
    'Criterion'
  ])
  .service('CriteriaGroupService', [
    '$q',
    'CriterionService',
    CriteriaGroupService
  ])
  .controller('CriteriaGroupCtrl', [
    'Translator',
    'ConfirmService',
    'CriteriaGroupService',
    'CriterionService',
    CriteriaGroupCtrl
  ])
  .directive('criteriaGroup', [
    () => new CriteriaGroupDirective
  ])
