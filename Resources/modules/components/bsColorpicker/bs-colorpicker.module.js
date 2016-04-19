import register from '../../utils/register'
import bsColorpickerDirective from './bs-colorpicker.directive'

let registerApp = new register('bs.colorpicker', [])
registerApp
  .directive('bsColorpicker', bsColorpickerDirective)