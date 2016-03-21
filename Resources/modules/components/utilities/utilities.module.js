import angular from 'angular/index'
import UtilityFunctionsFactory from './utility-functions.factory'
import iframeHeightOnLoadDirective from './iframe-height-on-load.directive'

angular.module('components.utilities', [])
  .factory('utilityFunctions', UtilityFunctionsFactory)
  .directive('iframeHeightOnLoad', iframeHeightOnLoadDirective)

