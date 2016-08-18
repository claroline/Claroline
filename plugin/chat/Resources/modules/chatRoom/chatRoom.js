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
import '../user/user'
import '../xmpp/xmpp'
import '#/main/core/scrollbar/module'
import '#/main/core/form/module'

import Routing from './routing'
import ChatRoomArchiveCtrl from './Controller/ChatRoomArchiveCtrl'
import ChatRoomInitCtrl from './Controller/ChatRoomInitCtrl'
import ChatRoomTextCtrl from './Controller/ChatRoomTextCtrl'
import ChatRoomVideoCtrl from './Controller/ChatRoomVideoCtrl'
import ChatRoomAudioCtrl from './Controller/ChatRoomAudioCtrl'
import ChatConfigureCtrl from './Controller/ChatConfigureCtrl'
import ChatRoomService from './Service/ChatRoomService'
import RTCService from './Service/RTCService'
import ChatRoomInputDirective from './Directive/ChatRoomInputDirective'
import ChatRoomMessagesDirective from './Directive/ChatRoomMessagesDirective'
import ChatRoomUsersDirective from './Directive/ChatRoomUsersDirective'
import ChatRoomVideosDirective from './Directive/ChatRoomVideosDirective'
import ChatRoomAudiosDirective from './Directive/ChatRoomAudiosDirective'
import Interceptors from '#/main/core/interceptorsDefault'

angular.module('ChatRoomModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation',
  'ui.scrollbar',
  'ui.router',
  'XmppModule',
  'FormBuilder',
  'UserModule'
])
  .controller('ChatRoomInitCtrl', ['$state', 'ChatRoomService', ChatRoomInitCtrl])
  .controller('ChatRoomArchiveCtrl', ['$state', '$uibModal', '$rootScope', 'ChatRoomService', 'FormBuilderService', ChatRoomArchiveCtrl])
  .controller('ChatRoomTextCtrl', ['$state', '$uibModal', '$rootScope', 'ChatRoomService', 'FormBuilderService', ChatRoomTextCtrl])
  .controller('ChatRoomAudioCtrl', ['$state', '$uibModal', '$log', '$rootScope', 'ChatRoomService', 'RTCService', 'FormBuilderService', ChatRoomAudioCtrl])
  .controller('ChatRoomVideoCtrl', ['$state', '$uibModal', '$log', '$rootScope', 'ChatRoomService', 'RTCService', 'FormBuilderService', ChatRoomVideoCtrl])
  .controller('ChatConfigureCtrl', ChatConfigureCtrl)
  .service('ChatRoomService', ChatRoomService)
  .service('RTCService', RTCService)
  .directive('chatRoomInput', () => new ChatRoomInputDirective)
  .directive('chatRoomMessages', () => new ChatRoomMessagesDirective)
  .directive('chatRoomUsers', () => new ChatRoomUsersDirective)
  .directive('chatRoomVideos', () => new ChatRoomVideosDirective)
  .directive('chatRoomAudios', () => new ChatRoomAudiosDirective)
  .config(Routing)
  .config(Interceptors)
