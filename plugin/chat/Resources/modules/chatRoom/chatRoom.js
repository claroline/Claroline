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
import '../message/message'
import '../user/user'
import '../xmpp/xmpp'
import '#/main/core/scrollbar/module'
import '#/main/core/form/module'

import Routing from './routing.js'
import ChatRoomArchiveCtrl from './Controller/ChatRoomArchiveCtrl'
import ChatRoomInitCtrl from './Controller/ChatRoomInitCtrl'
import ChatRoomTextCtrl from './Controller/ChatRoomTextCtrl'
import ChatRoomVideoCtrl from './Controller/ChatRoomVideoCtrl'
import ChatRoomAudioCtrl from './Controller/ChatRoomAudioCtrl'
import ChatConfigureCtrl from './Controller/ChatConfigureCtrl'
import ChatRoomService from './Service/ChatRoomService'
import VideoService from './Service/VideoService'
import ChatRoomInputDirective from './Directive/ChatRoomInputDirective'
import ChatRoomMessagesDirective from './Directive/ChatRoomMessagesDirective'
import ChatRoomUsersDirective from './Directive/ChatRoomUsersDirective'
import ChatRoomVideosDirective from './Directive/ChatRoomVideosDirective'
import ChatRoomAudiosDirective from './Directive/ChatRoomAudiosDirective.js'

angular.module('ChatRoomModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation',
  'ui.scrollbar',
  'ui.router',
  'XmppModule',
  'MessageModule',
  'FormBuilder',
  'UserModule'
])
  .controller('ChatRoomInitCtrl', ['$state', 'ChatRoomService', ChatRoomInitCtrl])
  .controller('ChatRoomArchiveCtrl', ['ChatRoomService', 'FormBuilderService', '$uibModal', '$state', ChatRoomArchiveCtrl])
  .controller('ChatRoomTextCtrl', ['$state', '$uibModal', 'ChatRoomService', 'FormBuilderService', ChatRoomTextCtrl])
  .controller('ChatRoomAudioCtrl', ['$state', '$uibModal', '$log', 'ChatRoomService', 'VideoService', 'FormBuilderService', ChatRoomAudioCtrl])
  .controller('ChatRoomVideoCtrl', ['$state', '$uibModal', '$log', 'ChatRoomService', 'VideoService', 'FormBuilderService', ChatRoomVideoCtrl])
  .controller('ChatConfigureCtrl', ChatConfigureCtrl)
  .service('ChatRoomService', ChatRoomService)
  .service('VideoService', VideoService)
  .directive('chatRoomInput', () => new ChatRoomInputDirective)
  .directive('chatRoomMessages', () => new ChatRoomMessagesDirective)
  .directive('chatRoomUsers', () => new ChatRoomUsersDirective)
  .directive('chatRoomVideos', () => new ChatRoomVideosDirective)
  .directive('chatRoomAudios', () => new ChatRoomAudiosDirective)
  .config(Routing)
