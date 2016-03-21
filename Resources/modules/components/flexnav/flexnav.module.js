import angular from 'angular/index'
import uiFlexnav from './flexnav.directive'
import uiFlexnavSubmenu from './flexnav-submenu.directive'
import FlexnavUtils from './flexnav-utils.service'
import FlexnavOptions from './flexnav-options.service'
import FlexnavTemplates from './flexnav.tpl.js'

angular
  .module('ui.flexnav', [])
  .directive('uiFlexnav', uiFlexnav)
  .directive('uiFlexnavSubmenu', uiFlexnavSubmenu)
  .factory('flexnav.utils',FlexnavUtils)
  .value('flexnav.options', FlexnavOptions)
  .run(FlexnavTemplates.append)