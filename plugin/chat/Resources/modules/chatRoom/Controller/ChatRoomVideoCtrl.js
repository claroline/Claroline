/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class ChatRoomVideoCtrl {

  constructor($state, ChatRoomService) {
    this.$state = $state
    this.ChatRoomService = ChatRoomService
    this.chatRoomConfig = ChatRoomService.getConfig()
    this.xmppConfig = ChatRoomService.getXmppConfig()
    this.initialize()
  }

  initialize () {
    console.log('*********** status video ***********')
    console.log(this.xmppConfig)
    console.log(this.chatRoomConfig)
  }

  goBack () {
    this.$state.transitionTo(
      'main',
      {},
      { reload: true, inherit: true, notify: true }
    )
  }
}