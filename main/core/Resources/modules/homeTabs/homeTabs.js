/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import angular from 'angular/index'

import 'angular-ui-bootstrap'
import 'angular-bootstrap-colorpicker'
import '#/main/core/innova/angular-translation'

import '../services/module'
import '../widgets/widgets'
import UserHomeTabCreationModalCtrl from './Controller/UserHomeTabCreationModalCtrl'
import UserHomeTabEditionModalCtrl from './Controller/UserHomeTabEditionModalCtrl'
import AdminHomeTabCreationModalCtrl from './Controller/AdminHomeTabCreationModalCtrl'
import AdminHomeTabEditionModalCtrl from './Controller/AdminHomeTabEditionModalCtrl'
import WorkspaceHomeTabCreationModalCtrl from './Controller/WorkspaceHomeTabCreationModalCtrl'
import WorkspaceHomeTabEditionModalCtrl from './Controller/WorkspaceHomeTabEditionModalCtrl'
import HomeTabService from './Service/HomeTabService'
import DesktopHomeTabsDirective from './Directive/DesktopHomeTabsDirective'
import AdminHomeTabsDirective from './Directive/AdminHomeTabsDirective'
import WorkspaceHomeTabsDirective from './Directive/WorkspaceHomeTabsDirective'

angular.module('HomeTabsModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'colorpicker.module',
  'ui.translation',
  'ClarolineAPI',
  'WidgetsModule'
])
.controller('UserHomeTabCreationModalCtrl', UserHomeTabCreationModalCtrl)
.controller('UserHomeTabEditionModalCtrl', UserHomeTabEditionModalCtrl)
.controller('AdminHomeTabCreationModalCtrl', AdminHomeTabCreationModalCtrl)
.controller('AdminHomeTabEditionModalCtrl', AdminHomeTabEditionModalCtrl)
.controller('WorkspaceHomeTabCreationModalCtrl', WorkspaceHomeTabCreationModalCtrl)
.controller('WorkspaceHomeTabEditionModalCtrl', WorkspaceHomeTabEditionModalCtrl)
.service('HomeTabService', HomeTabService)
.directive('desktopHomeTabs', () => new DesktopHomeTabsDirective)
.directive('adminHomeTabs', () => new AdminHomeTabsDirective)
.directive('workspaceHomeTabs', () => new WorkspaceHomeTabsDirective)