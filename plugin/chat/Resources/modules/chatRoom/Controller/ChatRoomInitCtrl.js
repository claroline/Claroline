/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class ChatRoomInitCtrl {

  constructor ($state, ChatRoomService) {
    this.$state = $state
    this.ChatRoomService = ChatRoomService
    this.chatRoomConfig = ChatRoomService.getConfig()
    this.xmppConfig = ChatRoomService.getXmppConfig()

    if (this.chatRoomConfig['chatRoom']['room_status_text'] === 'closed') {
      this.$state.transitionTo('archive', {}, { reload: true, inherit: true, notify: true })
    }

    this.initialize()
  }

  initialize () {
    if (!this.xmppConfig['connected']) {
      this.ChatRoomService.connect()
    }
  }

  initializeChatRoom () {
    this.initialize()
    this.ChatRoomService.initializeRoom()
  }

  connectToRoom () {
    this.initialize()
    this.$state.transitionTo(this.chatRoomConfig['chatRoom']['room_type_text'], {}, {reload: true, inherit: true, notify: true})
  }
}
