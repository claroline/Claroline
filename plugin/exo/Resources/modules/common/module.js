/**
 * Common module
 * Share data service
 */

import 'angular-sanitize'

import DateStringFilter from './Filters/DateStringFilter'
import RouterFilter from './Filters/RouterFilter'
import SimpleTypeFilter from './Filters/SimpleTypeFilter'
import UnsafeFilter from './Filters/UnsafeFilter'
import CommonService from './Services/CommonService'
import TinyMceService from './Services/TinyMceService'

angular
  .module('Common', [
      'ngSanitize'
  ])
  .filter('date_string', [
      '$filter',
      DateStringFilter
  ])
  .filter('path', [
      RouterFilter
  ])
  .filter('simple_type', [
      'CommonService',
      SimpleTypeFilter
  ])
  .filter('unsafe', [
      '$sce',
      UnsafeFilter
  ])
  .service('CommonService', [
      CommonService
  ])
  .service('TinyMceService', [
      TinyMceService
  ])
