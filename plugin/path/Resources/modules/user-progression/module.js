/**
 * User Progression module
 */

import angular from 'angular/index'

import '../alert/module'

import UserProgressionService from './Service/UserProgressionService'

angular
  .module('UserProgression', [
    'Alert'
  ])
  .service('UserProgressionService', [
    '$http',
    '$q',
    'AlertService',
    UserProgressionService
  ])