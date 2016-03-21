import angular from 'angular/index'
import UtilityFunctionsFactory from 'UtilityFunctionsFactory'
import iframeHeightOnLoadDirective from 'iframeHeightOnLoadDirective'

angular.module('components.utilities', [])
  .factory('utilityFunctions', UtilityFunctionsFactory)
  .directive('iframeHeightOnLoad', iframeHeightOnLoadDirective)

