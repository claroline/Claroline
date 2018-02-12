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
import '#/main/core/innova/angular-translation'

import SessionEventService from './Service/SessionEventService'
import SessionEventCreationModalCtrl from './Controller/SessionEventCreationModalCtrl'
import SessionEventEditionModalCtrl from './Controller/SessionEventEditionModalCtrl'
import SessionEventRepeatModalCtrl from './Controller/SessionEventRepeatModalCtrl'
import SessionEventCommentsManagementModalCtrl from './Controller/SessionEventCommentsManagementModalCtrl'
import SessionEventUsersRegistrationModalCtrl from './Controller/SessionEventUsersRegistrationModalCtrl'
import SessionEventUsersExportModalCtrl from './Controller/SessionEventUsersExportModalCtrl'

angular.module('SessionEventModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation'
])
.service('SessionEventService', SessionEventService)
.controller('SessionEventCreationModalCtrl', SessionEventCreationModalCtrl)
.controller('SessionEventEditionModalCtrl', SessionEventEditionModalCtrl)
.controller('SessionEventRepeatModalCtrl', SessionEventRepeatModalCtrl)
.controller('SessionEventCommentsManagementModalCtrl', SessionEventCommentsManagementModalCtrl)
.controller('SessionEventUsersRegistrationModalCtrl', SessionEventUsersRegistrationModalCtrl)
.controller('SessionEventUsersExportModalCtrl', SessionEventUsersExportModalCtrl)