/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import 'angular/index'

import bootstrap from 'angular-bootstrap'
import translation from 'angular-ui-translation/angular-translation'
import MessageService from './Service/MessageService'

angular.module('MessageModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation'
])
.service('MessageService', MessageService)

