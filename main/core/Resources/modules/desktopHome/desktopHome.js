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
import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'

import '../homeTabs/homeTabs'
import '../widgets/widgets'
import Routing from './routing.js'
import DesktopHomeMainCtrl from './Controller/DesktopHomeMainCtrl'

angular.module('DesktopHomeModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation',
  'ui.router',
  'HomeTabsModule',
  'WidgetsModule'
])
.controller('DesktopHomeMainCtrl', ['$http', '$stateParams', '$state', 'HomeTabService', 'WidgetService', DesktopHomeMainCtrl])
.config(Routing)
