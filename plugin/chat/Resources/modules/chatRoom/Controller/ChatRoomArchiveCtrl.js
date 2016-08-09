/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import configureTpl from '../Partial/configure.html'
import ChatRoomBaseCtrl from './ChatRoomBaseCtrl'

export default class ChatRoomArchiveCtrl extends ChatRoomBaseCtrl {
  constructor ($state, $uibModal, $log, ChatRoomService, VideoService, FormBuilderService) {
    super($state, $uibModal, $log, ChatRoomService, VideoService, FormBuilderService)
    this.oldMessages = this.ChatRoomService.getRegisteredMessages()
  }
}
