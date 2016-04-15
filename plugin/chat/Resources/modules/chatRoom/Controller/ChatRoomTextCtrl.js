/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class ChatRoomTextCtrl {

  constructor($state, ChatRoomService) {
    this.$state = $state
    this.ChatRoomService = ChatRoomService
    this.chatRoomConfig = ChatRoomService.getConfig()
    this.xmppConfig = ChatRoomService.getXmppConfig()
    this.messages = ChatRoomService.getMessages()
    this.users = ChatRoomService.getUsers()
    this.bannedUsers = ChatRoomService.getBannedUsers()
    this.input = ''
    this.initialize()

    $(window).unload(($event) => {
      $event.preventDefault()
      console.log('Disconnecting...')
      this.ChatRoomService.disconnectFromRoom()
    })
  }

  initialize () {
    console.log('*********** status text ***********')
    console.log(this.xmppConfig)
    console.log(this.chatRoomConfig)
    console.log('*********** END status text ***********')

    if (!this.chatRoomConfig['connected']) {
      this.ChatRoomService.connectToRoom()
    }
  }

  kickUser (username) {
    this.ChatRoomService.kickUser(username)
  }

  muteUser (username) {
    this.ChatRoomService.muteUser(username)
  }

  unmuteUser (username) {
    this.ChatRoomService.unmuteUser(username)
  }

  banUser (username) {
    this.ChatRoomService.banUser(username)
  }

  unbanUser (username) {
    this.ChatRoomService.unbanUser(username)
  }

  isAdmin () {
    return this.ChatRoomService.isAdmin()
  }

  isModerator () {
    return this.ChatRoomService.isModerator()
  }

  canParticipate () {
    return this.ChatRoomService.canParticipate()
  }

  goBack () {
    this.$state.transitionTo(
      'main',
      {},
      { reload: true, inherit: true, notify: true }
    )
  }

  disconnect () {
    this.ChatRoomService.disconnectFromRoom()
  }

  sendMessage () {
    this.ChatRoomService.sendMessage(this.input)
    this.input = ''
  }
}