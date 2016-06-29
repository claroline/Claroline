/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class ChatRoomMainCtrl {

  constructor($state, ChatRoomService) {
    this.$state = $state
    this.ChatRoomService = ChatRoomService
    this.chatRoomConfig = ChatRoomService.getConfig()
    this.xmppConfig = ChatRoomService.getXmppConfig()
    this.initialize()
  }

  initialize () {
    this.ChatRoomService.connect()
  }

  initializeChatRoom () {
    if (this.xmppConfig['connected']) {
      this.ChatRoomService.initializeRoom()
      //this.ChatRoomService.connectToRoom()
      //this.ChatRoomService.initializeRoom()
    }
  }

  connectToRoom () {
    //this.ChatRoomService.connectToRoom()

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
    }
  }
}