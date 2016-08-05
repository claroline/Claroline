/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import $ from 'jquery'
import ChatRoomBaseCtrl from './ChatRoomBaseCtrl'

export default class ChatRoomVideoCtrl extends ChatRoomBaseCtrl {

  constructor($state, $log, ChatRoomService, VideoService) {
    super($state, ChatRoomService)
    this.$log = $log
    this.VideoService = VideoService
    this.videoConfig = VideoService.getVideoConfig()

    //this should be only loaded once
    $(window).unload(($event) => {
      $event.preventDefault()
      this.$log.log('Disconnecting...')
      this.VideoService.closeAllConnections()
    })
  }

  goBack () {
    this.VideoService.closeAllConnections()
    this.VideoService.stopMedia()
    this.$log.log('All connection closed...')
    this.ChatRoomService.disconnectFromRoom()
    this.$state.transitionTo(
      'main',
      {},
      { reload: true, inherit: true, notify: true }
    )
  }

  switchAudio (username) {
    this.VideoService.requestUserMicroSwitch(username)
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

  getStreamClass (username) {
    const selectedClass = this.videoConfig['selectedUser'] === username ? 'video-selected' : ''
    const speakingClass = this.videoConfig['speakingUser'] === username ? 'video-speaking' : ''

    return `${selectedClass} ${speakingClass}`
  }
}
