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
import 'angular-data-table/release/dataTable.helpers.min'
import 'angular-ui-bootstrap'
import '#/main/core/innova/angular-translation'

import Routing from './routing.js' 
import CursusDescriptionModalCtrl from './Cursus/Controller/CursusDescriptionModalCtrl'
import CursusGroupUnregistrationModalCtrl from './Cursus/Controller/CursusGroupUnregistrationModalCtrl'
import CursusGroupsListRegistrationModalCtrl from './Cursus/Controller/CursusGroupsListRegistrationModalCtrl'
import CursusGroupsUnregistrationModalCtrl from './Cursus/Controller/CursusGroupsUnregistrationModalCtrl'
import CursusRegistrationCtrl from './Cursus/Controller/CursusRegistrationCtrl'
import CursusRegistrationManagementCtrl from './Cursus/Controller/CursusRegistrationManagementCtrl'
import CursusRegistrationSearchCtrl from './Cursus/Controller/CursusRegistrationSearchCtrl'
import CursusRegistrationSessionsModalCtrl from './Cursus/Controller/CursusRegistrationSessionsModalCtrl'
import CursusUserUnregistrationModalCtrl from './Cursus/Controller/CursusUserUnregistrationModalCtrl'
import CursusUsersUnregistrationModalCtrl from './Cursus/Controller/CursusUsersUnregistrationModalCtrl'
import SimpleModalCtrl from './Cursus/Controller/SimpleModalCtrl'
import CursusRegistrationGroupsDirective from './Cursus/Directive/CursusRegistrationGroupsDirective'
import CursusRegistrationInformationsDirective from './Cursus/Directive/CursusRegistrationInformationsDirective'
import CursusRegistrationListDirective from './Cursus/Directive/CursusRegistrationListDirective'
import CursusRegistrationUsersDirective from './Cursus/Directive/CursusRegistrationUsersDirective'

import CursusQueueManagementCtrl from './Queue/Controller/CursusQueueManagementCtrl'
import SessionsChoicesTransferModalCtrl from './Queue/Controller/SessionsChoicesTransferModalCtrl'
import RegistrationQueueCoursesDirective from './Queue/Directive/RegistrationQueueCoursesDirective'

angular.module('CursusRegistrationModule', [
  'ui.router',
  'ui.translation',
  'data-table',
  'ui.bootstrap',
  'ui.bootstrap.tpls'
])
.controller('CursusRegistrationCtrl', ['$http', CursusRegistrationCtrl])
.controller('CursusRegistrationManagementCtrl', ['$stateParams', '$http', '$uibModal', CursusRegistrationManagementCtrl])
.controller('CursusRegistrationSearchCtrl', ['$stateParams', '$http', '$uibModal', CursusRegistrationSearchCtrl])
.controller('CursusDescriptionModalCtrl', CursusDescriptionModalCtrl)
.controller('CursusGroupUnregistrationModalCtrl', CursusGroupUnregistrationModalCtrl)
.controller('CursusGroupsListRegistrationModalCtrl', CursusGroupsListRegistrationModalCtrl)
.controller('CursusGroupsUnregistrationModalCtrl', CursusGroupsUnregistrationModalCtrl)
.controller('CursusRegistrationSessionsModalCtrl', CursusRegistrationSessionsModalCtrl)
.controller('CursusUserUnregistrationModalCtrl', CursusUserUnregistrationModalCtrl)
.controller('CursusUsersUnregistrationModalCtrl', CursusUsersUnregistrationModalCtrl)
.controller('SimpleModalCtrl', SimpleModalCtrl)
.directive('cursusRegistrationGroups', () => new CursusRegistrationGroupsDirective)
.directive('cursusRegistrationInformations', () => new CursusRegistrationInformationsDirective)
.directive('cursusList', () => new CursusRegistrationListDirective)
.directive('cursusRegistrationUsers', () => new CursusRegistrationUsersDirective)
.controller('CursusQueueManagementCtrl', ['$http', '$uibModal', CursusQueueManagementCtrl])
.controller('SessionsChoicesTransferModalCtrl', SessionsChoicesTransferModalCtrl)
.directive('registrationQueueCourses', () => new RegistrationQueueCoursesDirective)
.config(Routing)