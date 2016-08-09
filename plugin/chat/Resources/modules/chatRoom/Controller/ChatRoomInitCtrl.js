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

    if (this.chatRoomConfig['roomStatus'] === 'closed') {
      this.$state.transitionTo('archive', {}, { reload: true, inherit: true, notify: true } )    
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
    if (this.chatRoomConfig['roomType'] === 'text') {
      this.$state.transitionTo(
        'text',
        {},
        { reload: true, inherit: true, notify: true }
      )
    } else if (this.chatRoomConfig['roomType'] === 'video') {
      this.$state.transitionTo(
        'video',
        {},
        { reload: true, inherit: true, notify: true }
      )
  } else if (this.chatRoomConfig['roomType'] === 'audio') {
      this.$state.transitionTo(
        'audio',
        {},
        { reload: true, inherit: true, notify: true }
      )
    }
  }
}
