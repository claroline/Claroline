/**
 * Criterion module
 */

import angular from 'angular/index'

import './../confirm/module'
import './../path/module'

import CriterionService from './Service/CriterionService'
import ActivityAttemptCriterion from './Service/Type/ActivityAttemptCriterion'
import ActivityStatusCriterion from './Service/Type/ActivityStatusCriterion'
import GroupCriterion from './Service/Type/GroupCriterion'
import TeamCriterion from './Service/Type/TeamCriterion'
import CriterionCtrl from './Controller/CriterionCtrl'
import CriterionDirective from './Directive/CriterionDirective'

angular
  .module('Criterion', [
    'Confirm',
    'Path'
  ])
  .service('CriterionService', [
    'ActivityAttemptCriterion',
    'ActivityStatusCriterion',
    'GroupCriterion',
    'TeamCriterion',
    CriterionService
  ])
  .service('ActivityAttemptCriterion', [
    '$log',
    '$q',
    '$http',
    ActivityAttemptCriterion
  ])
  .service('ActivityStatusCriterion', [
    '$log',
    '$q',
    '$http',
    ActivityStatusCriterion
  ])
  .service('GroupCriterion', [
    '$log',
    '$q',
    '$http',
    GroupCriterion
  ])
  .service('TeamCriterion', [
    '$log',
    '$q',
    '$http',
    'PathService',
    TeamCriterion
  ])
  .controller('CriterionCtrl', [
    'ConfirmService',
    'CriterionService',
    CriterionCtrl
  ])
  .directive('criterion', [
    () => new CriterionDirective
  ])