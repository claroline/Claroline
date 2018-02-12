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

import '#/main/core/services/module'
import '#/main/core/scaffolding/asset/module'
import '#/main/core/form/module'
import CourseService from './Service/CourseService'
import CourseCreationModalCtrl from './Controller/CourseCreationModalCtrl'
import CourseEditionModalCtrl from './Controller/CourseEditionModalCtrl'
import CoursesImportModalCtrl from './Controller/CoursesImportModalCtrl'
import CourseViewModalCtrl from './Controller/CourseViewModalCtrl'

angular.module('CourseModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'colorpicker.module',
  'ui.translation',
  'ClarolineAPI',
  'ui.asset',
  'FormBuilder'
])
.service('CourseService', CourseService)
.controller('CourseCreationModalCtrl', CourseCreationModalCtrl)
.controller('CourseEditionModalCtrl', CourseEditionModalCtrl)
.controller('CoursesImportModalCtrl', CoursesImportModalCtrl)
.controller('CourseViewModalCtrl', CourseViewModalCtrl)