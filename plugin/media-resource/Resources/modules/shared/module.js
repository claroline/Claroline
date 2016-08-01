/**
 * Shared module
 * Share services and data
 */
import angular from 'angular/index'
import 'angular-sanitize'
import '#/main/core/fos-js-router/module'
import SecondsToHmsFilter from './Filters/SecondsToHmsFilter'
import ConfigService from './Services/ConfigService'
import RegionsService from './Services/RegionsService'
import UserService from './Services/UserService'

angular
  .module('Shared', [
    'ngSanitize',
    'ui.fos-js-router'
  ])
  .filter('secondsToHms', [
    SecondsToHmsFilter
  ])
  .service('configService', [
    '$filter',
    ConfigService
  ])
  .service('regionsService', [
    RegionsService
  ])
  .service('userService', [
    '$http',
    '$q',
    'url',
    UserService
  ])
