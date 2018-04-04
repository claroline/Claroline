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
import DesktopWidgetInstanceCreationModalCtrl from './Controller/DesktopWidgetInstanceCreationModalCtrl'
import DesktopWidgetInstanceEditionModalCtrl from './Controller/DesktopWidgetInstanceEditionModalCtrl'
import AdminWidgetInstanceCreationModalCtrl from './Controller/AdminWidgetInstanceCreationModalCtrl'
import AdminWidgetInstanceEditionModalCtrl from './Controller/AdminWidgetInstanceEditionModalCtrl'
import WorkspaceWidgetInstanceCreationModalCtrl from './Controller/WorkspaceWidgetInstanceCreationModalCtrl'
import WorkspaceWidgetInstanceEditionModalCtrl from './Controller/WorkspaceWidgetInstanceEditionModalCtrl'
import WidgetService from './Service/WidgetService'
import WidgetsDirective from './Directive/WidgetsDirective'
import AdminWidgetsDirective from './Directive/AdminWidgetsDirective'
import WorkspaceWidgetsDirective from './Directive/WorkspaceWidgetsDirective'

angular.module('WidgetsModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'colorpicker.module',
  'ui.translation',
  'ClarolineAPI',
  'gridster'
])
.controller('DesktopWidgetInstanceCreationModalCtrl', DesktopWidgetInstanceCreationModalCtrl)
.controller('DesktopWidgetInstanceEditionModalCtrl', DesktopWidgetInstanceEditionModalCtrl)
.controller('AdminWidgetInstanceCreationModalCtrl', AdminWidgetInstanceCreationModalCtrl)
.controller('AdminWidgetInstanceEditionModalCtrl', AdminWidgetInstanceEditionModalCtrl)
.controller('WorkspaceWidgetInstanceCreationModalCtrl', WorkspaceWidgetInstanceCreationModalCtrl)
.controller('WorkspaceWidgetInstanceEditionModalCtrl', WorkspaceWidgetInstanceEditionModalCtrl)
.service('WidgetService', WidgetService)
.directive('widgets', () => new WidgetsDirective)
.directive('adminWidgets', () => new AdminWidgetsDirective)
.directive('workspaceWidgets', () => new WorkspaceWidgetsDirective)
