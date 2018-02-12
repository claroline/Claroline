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
import 'angular-ui-tinymce'

import SessionService from './Service/SessionService'
import SessionCreationModalCtrl from './Controller/SessionCreationModalCtrl'
import SessionEditionModalCtrl from './Controller/SessionEditionModalCtrl'
import SessionDeletionModalCtrl from './Controller/SessionDeletionModalCtrl'
import UsersRegistrationModalCtrl from './Controller/UsersRegistrationModalCtrl'
import GroupsRegistrationModalCtrl from './Controller/GroupsRegistrationModalCtrl'
import SessionMessageModalCtrl from './Controller/SessionMessageModalCtrl'
import SessionUsersExportModalCtrl from './Controller/SessionUsersExportModalCtrl'

angular.module('SessionModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'colorpicker.module',
  'ui.translation',
  'ui.tinymce'
])
.service('SessionService', SessionService)
.controller('SessionCreationModalCtrl', SessionCreationModalCtrl)
.controller('SessionEditionModalCtrl', SessionEditionModalCtrl)
.controller('SessionDeletionModalCtrl', SessionDeletionModalCtrl)
.controller('UsersRegistrationModalCtrl', UsersRegistrationModalCtrl)
.controller('GroupsRegistrationModalCtrl', GroupsRegistrationModalCtrl)
.controller('SessionMessageModalCtrl', SessionMessageModalCtrl)
.controller('SessionUsersExportModalCtrl', SessionUsersExportModalCtrl)
.directive('datetimepickerNeutralTimezone', function () {
  return {
    restrict: 'A',
    priority: 1,
    require: 'ngModel',
    link: (scope, element, attrs, ctrl) => {
      ctrl.$formatters.push((value) => {
        let date = new Date(Date.parse(value))
        date = new Date(date.getTime() + (60000 * date.getTimezoneOffset()))

        return date
      })

      ctrl.$parsers.push((value) => {
        const date = new Date(value.getTime() - (60000 * value.getTimezoneOffset()))

        return date
      })
    }
  }
})