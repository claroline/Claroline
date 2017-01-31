/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import angular from 'angular/index'

import 'angular-bootstrap'
import 'angular-bootstrap-colorpicker'
import 'angular-ui-translation/angular-translation'

import '#/main/core/services/module'
import CategoryCreationModalCtrl from './Controller/CategoryCreationModalCtrl'
import CategoryEditionModalCtrl from './Controller/CategoryEditionModalCtrl'
import CategoryService from './Service/CategoryService'

angular.module('CategoryModule', [
  'ui.translation',
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'colorpicker.module',
  'ClarolineAPI'
])
.service('CategoryService', CategoryService)
.controller('CategoryCreationModalCtrl', CategoryCreationModalCtrl)
.controller('CategoryEditionModalCtrl', CategoryEditionModalCtrl)