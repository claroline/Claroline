/**
 * Utils module
 */

import angular from 'angular/index'

import tinymceConfig from './Service/TinymceConfigService'
import IdentifierService from './Service/IdentifierService'
import truncateFilter from './Filter/TruncateFilter'
import trustAsHtmlFilter from './Filter/TrustAsHtmlFilter'

angular
  .module('Utils', [])
  .factory('tinymceConfig', [
    tinymceConfig
  ])
  .service('IdentifierService', [
    IdentifierService
  ])
  .filter('truncate', [
    truncateFilter
  ])
  .filter('trustAsHtml', [
    '$sce',
    trustAsHtmlFilter
  ])