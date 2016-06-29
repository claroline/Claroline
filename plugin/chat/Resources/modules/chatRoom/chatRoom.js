/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import 'angular/index'

import UIRouter from 'angular-ui-router'
import bootstrap from 'angular-bootstrap'
import translation from 'angular-ui-translation/angular-translation'

import MessageModule from '../message/message'
import UserModule from '../user/user'
import XmppModule from '../xmpp/xmpp'
import Routing from './routing.js'
import ChatRoomMainCtrl from './Controller/ChatRoomMainCtrl'
import ChatRoomTextCtrl from './Controller/ChatRoomTextCtrl'
import ChatRoomVideoCtrl from './Controller/ChatRoomVideoCtrl'
import ChatRoomService from './Service/ChatRoomService'
import VideoService from './Service/VideoService'
import ChatRoomInputDirective from './Directive/ChatRoomInputDirective'
import ChatRoomMessagesDirective from './Directive/ChatRoomMessagesDirective'
import ChatRoomUsersDirective from './Directive/ChatRoomUsersDirective'
import ChatRoomVideosDirective from './Directive/ChatRoomVideosDirective'

angular.module('ChatRoomModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'ui.translation',
  'ui.router',
  'XmppModule',
  'MessageModule',
  'UserModule'
])
.controller('ChatRoomMainCtrl', ['$state', 'ChatRoomService', ChatRoomMainCtrl])
.controller('ChatRoomTextCtrl', ['$state', 'ChatRoomService', ChatRoomTextCtrl])
.controller('ChatRoomVideoCtrl', ['$state', 'ChatRoomService', 'VideoService', ChatRoomVideoCtrl])
.service('ChatRoomService', ChatRoomService)
.service('VideoService', VideoService)
.directive('chatRoomInput', () => new ChatRoomInputDirective)
.directive('chatRoomMessages', () => new ChatRoomMessagesDirective)
.directive('chatRoomUsers', () => new ChatRoomUsersDirective)
.directive('chatRoomVideos', () => new ChatRoomVideosDirective)
.config(Routing)