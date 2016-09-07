/**
 * Criterion module
 */

import angular from 'angular/index'

import '#/main/core/fos-js-router/module'
import '#/main/core/translation/module'
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
    'ui.fos-js-router',
    'translation',
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
    'Translator',
    'url',
    ActivityAttemptCriterion
  ])
  .service('ActivityStatusCriterion', [
    '$log',
    '$q',
    '$http',
    'Translator',
    'url',
    ActivityStatusCriterion
  ])
  .service('GroupCriterion', [
    '$log',
    '$q',
    '$http',
    'Translator',
    'url',
    GroupCriterion
  ])
  .service('TeamCriterion', [
    '$log',
    '$q',
    '$http',
    'Translator',
    'url',
    'PathService',
    TeamCriterion
  ])
  .controller('CriterionCtrl', [
    'Translator',
    'ConfirmService',
    'CriterionService',
    CriterionCtrl
  ])
  .directive('criterion', [
    () => new CriterionDirective
  ])