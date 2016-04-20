import register from '../../utils/register'
import {} from '../utilities/utilities.module'
import resizerDirective from './resizer.directive'
import resizerBottomDirective from './resizer-bottom.directive'
import changeHeightDirective from './change-height.directive'
import resizerRightDirective from './resizer-right.directive'

let registerApp = new register('ui.resizer', [ 'components.utilities' ])

registerApp
  .directive('resizer', resizerDirective)
  .directive('resizerBottom', resizerBottomDirective)
  .directive('changeHeight', changeHeightDirective)
  .directive('resizerRight', resizerRightDirective)