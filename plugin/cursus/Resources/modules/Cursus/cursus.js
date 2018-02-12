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

import '../../../../../main/core/Resources/modules/services/module'
import '../../../../../main/core/Resources/modules/form/module'
import CursusService from './Service/CursusService'
import CursusCreationModalCtrl from './Controller/CursusCreationModalCtrl'
import CursusEditionModalCtrl from './Controller/CursusEditionModalCtrl'
import CursusImportModalCtrl from './Controller/CursusImportModalCtrl'
import CursusHierarchyModalCtrl from './Controller/CursusHierarchyModalCtrl'
import CursusCourseSelectionModalCtrl from './Controller/CursusCourseSelectionModalCtrl'

angular.module('CursusModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'colorpicker.module',
  'ui.translation',
  'ClarolineAPI',
  'FormBuilder'
])
.service('CursusService', CursusService)
.controller('CursusCreationModalCtrl', CursusCreationModalCtrl)
.controller('CursusEditionModalCtrl', CursusEditionModalCtrl)
.controller('CursusImportModalCtrl', CursusImportModalCtrl)
.controller('CursusHierarchyModalCtrl', CursusHierarchyModalCtrl)
.controller('CursusCourseSelectionModalCtrl', CursusCourseSelectionModalCtrl)
