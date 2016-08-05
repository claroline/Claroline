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

import Routing from './routing.js'
import ChatRoomInitCtrl from './Controller/ChatRoomInitCtrl'
import ChatRoomTextCtrl from './Controller/ChatRoomTextCtrl'
import ChatRoomVideoCtrl from './Controller/ChatRoomVideoCtrl'
import ChatRoomAudioCtrl from './Controller/ChatRoomAudioCtrl'
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
  'UserModule'
])
  .controller('ChatRoomInitCtrl', ['$state', 'ChatRoomService', ChatRoomInitCtrl])
  .controller('ChatRoomTextCtrl', ['$state', 'ChatRoomService', ChatRoomTextCtrl])
  .controller('ChatRoomAudioCtrl', ['$state', '$log', 'ChatRoomService', 'VideoService', ChatRoomAudioCtrl])
  .controller('ChatRoomVideoCtrl', ['$state', '$log', 'ChatRoomService', 'VideoService', ChatRoomVideoCtrl])
  .service('ChatRoomService', ChatRoomService)
  .service('VideoService', VideoService)
  .directive('chatRoomInput', () => new ChatRoomInputDirective)
  .directive('chatRoomMessages', () => new ChatRoomMessagesDirective)
  .directive('chatRoomUsers', () => new ChatRoomUsersDirective)
  .directive('chatRoomVideos', () => new ChatRoomVideosDirective)
  .directive('chatRoomAudios', () => new ChatRoomAudiosDirective)
  .config(Routing)
