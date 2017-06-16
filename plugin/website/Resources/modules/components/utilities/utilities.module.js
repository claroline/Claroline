import register from '../../utils/register'
import UtilityFunctions from './utility-functions.factory'
import iframeHeightOnLoadDirective from './iframe-height-on-load.directive'
let registerApp = new register('components.utilities', [])
registerApp
  .factory('utilityFunctions', UtilityFunctions)
  .directive('iframeHeightOnLoad', iframeHeightOnLoadDirective)
  .directive('convertToNumber', function () {
    return {
      require: 'ngModel',
      link: function (scope, element, attrs, ngModel) {
        ngModel.$parsers.push(function (val) {
          return parseInt(val, 10)
        })
        ngModel.$formatters.push(function (val) {
          return '' + val
        })
      }
    }
  })
