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

  constructor ($state, $uibModal, $log, $rootScope, ChatRoomService, RTCService, FormBuilderService) {
    super($state, $uibModal, $rootScope, ChatRoomService, FormBuilderService)
    this.$log = $log
    this.RTCService = RTCService
    this.FormBuilderService = FormBuilderService
    this.rtcConfig = RTCService.getVideoConfig()
    this.rtcConfig.myVideoEnabled = true
    this.rtcConfig.myAudioEnabled = true

    // the config must change to request approriate medias
    $rootScope.$on('$stateChangeStart', (event, toState) => {
      if (toState.name === 'video') {
        this.rtcConfig.myVideoEnabled = true
        this.rtcConfig.myAudioEnabled = true
        ChatRoomService.setConnectedCallback(RTCService._startMedias)
        ChatRoomService.setUserDisconnectedCallback(RTCService._stopUserStream)
        ChatRoomService.setManagementCallback(RTCService._manageManagementMessage)
      }
    })

    // this should be only loaded once
    $(window).unload(($event) => {
      $event.preventDefault()
      this.$log.log('Disconnecting...')
      this.RTCService.closeAllConnections()
    })
  }

  goBack () {
    this.RTCService.closeAllConnections()
    this.RTCService.stopMedia()
    this.$log.log('All connection closed...')
    this.ChatRoomService.disconnectFromRoom()
    this.$state.transitionTo(
      'main',
      {},
      { reload: true, inherit: true, notify: true }
    )
  }

  switchAudio (username) {
    this.RTCService.requestUserMicroSwitch(username)
  }

  switchVideo () {
    this.RTCService.switchVideo()
  }

  getMyUsername () {
    return this.chatRoomConfig['myUsername']
  }

  selectSourceStream (username) {
    this.RTCService.selectSourceStream(username)
  }

  getStreamClass (username) {
    const selectedClass = this.rtcConfig['selectedUser'] === username ? 'video-selected' : ''
    const speakingClass = this.rtcConfig['speakingUser'] === username ? 'video-speaking' : ''

    return `${selectedClass} ${speakingClass}`
  }
}
