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
    this.input = ''
    this.initialize()
  }

  initialize () {
    console.log('*********** status text ***********')
    console.log(this.xmppConfig)
    console.log(this.chatRoomConfig)
  }

  refresh () {
    console.log(this.chatRoomConfig)
    console.log(this.users)
    console.log(this.messages)
  }

  muteUser (username) {
    console.log('Mute ' + username)
  }

  unmuteUser (username) {
    console.log('Unmute ' + username)
  }

  kickUser (username) {
    console.log('Kick ' + username)
  }

  banUser (username) {
    console.log('Ban ' + username)
  }

  unbanUser (username) {
    console.log('Unban ' + username)
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

  sendMessage () {
    this.ChatRoomService.sendMessage(this.input)
    this.input = ''
  }
}