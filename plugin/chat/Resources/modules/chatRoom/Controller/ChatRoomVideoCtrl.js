/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class ChatRoomVideoCtrl {

  constructor($state, ChatRoomService, VideoService) {
    this.$state = $state
    this.ChatRoomService = ChatRoomService
    this.VideoService = VideoService
    this.chatRoomConfig = ChatRoomService.getConfig()
    this.xmppConfig = ChatRoomService.getXmppConfig()
    this.videoConfig = VideoService.getVideoConfig()
    this.messages = ChatRoomService.getMessages()
    this.oldMessages = ChatRoomService.getOldMessages()
    this.users = ChatRoomService.getUsers()
    this.bannedUsers = ChatRoomService.getBannedUsers()
    this.input = ''
    this.initialize()
  }

  initialize () {
    $(window).unload(($event) => {
      $event.preventDefault()
      console.log('Disconnecting...')
      this.ChatRoomService.disconnectFromRoom()
    })

    if (!this.chatRoomConfig['connected']) {
      this.ChatRoomService.connectToRoom()
    }
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
    this.ChatRoomService.disconnectFromRoom()
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

  switchCamera () {
    this.VideoService.switchCamera()
  }

  switchAudio () {
    this.VideoService.switchAudio()
  }

  switchVideo () {
    this.VideoService.switchVideo()
  }

  getMyUsername () {
    return this.chatRoomConfig['myUsername']
  }

  selectSourceStream (username) {
    this.VideoService.selectSourceStream(username)
  }
}