/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import angular from 'angular/index'

import 'angular-ui-router'
import 'angular-ui-bootstrap'
import '#/main/core/innova/angular-translation'

import '../../../homeTabs/homeTabs'
import '../../../widgets/widgets'
import Routing from './routing.js'
import WorkspaceHomeCtrl from './Controller/WorkspaceHomeCtrl'

angular.module('WorkspaceHomeModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation',
  'ui.router',
  'HomeTabsModule',
  'WidgetsModule'
])
.controller('WorkspaceHomeCtrl', ['$http', '$stateParams', '$state', 'HomeTabService', 'WidgetService', WorkspaceHomeCtrl])
.config(Routing)
