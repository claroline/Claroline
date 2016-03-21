import angular from 'angular/index'
import utilities from '../components/utilities/utilities.module'
import resizerDirective from 'resizerDirective'
import resizerBottomDirective from 'resizerBottomDirective'
import changeHeightDirective from 'changeHeightDirective'
import resizerRightDirective from 'resizerRightDirective'

angular.module('ui.resizer', [ 'components.utilities' ])
  .directive('resizer', resizerDirective)
  .directive('resizerBottom', resizerBottomDirective)
  .directive('changeHeight', changeHeightDirective)
  .directive('resizerRight', resizerRightDirective)