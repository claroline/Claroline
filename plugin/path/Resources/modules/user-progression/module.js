/**
 * User Progression module
 */

import angular from 'angular/index'

import '#/main/core/fos-js-router/module'
import '#/main/core/translation/module'
import '../alert/module'

import UserProgressionService from './Service/UserProgressionService'

angular
  .module('UserProgression', [
    'ui.fos-js-router',
    'translation',
    'Alert'
  ])
  .service('UserProgressionService', [
    '$http',
    '$q',
    'Translator',
    'url',
    'AlertService',
    UserProgressionService
  ])