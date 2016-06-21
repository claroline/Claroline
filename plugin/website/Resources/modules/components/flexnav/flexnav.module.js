import register from '../../utils/register'
import uiFlexnav from './flexnav.directive'
import uiFlexnavSubmenu from './flexnav-submenu.directive'
import FlexnavUtils from './flexnav-utils.factory'

let registerApp = new register('ui.flexnav', [])
registerApp
  .factory('flexnav.utils', FlexnavUtils)
  .directive('uiFlexnav', uiFlexnav)
  .directive('uiFlexnavSubmenu', uiFlexnavSubmenu)
