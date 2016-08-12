/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import ChatRoomVideoCtrl from './ChatRoomVideoCtrl'

export default class ChatRoomAudioCtrl extends ChatRoomVideoCtrl {
  constructor ($state, $uibModal, $log, $rootScope, ChatRoomService, RTCService, FormBuilderService) {
    super($state, $uibModal, $log, $rootScope, ChatRoomService, RTCService, FormBuilderService)

    this.rtcConfig.myVideoEnabled = false
    this.rtcConfig.myAudioEnabled = true

    // the config must change to request approriate medias
    $rootScope.$on('$stateChangeStart', (event, toState) => {
      if (toState.name === 'audio') {
        this.rtcConfig.myVideoEnabled = false
        this.rtcConfig.myAudioEnabled = true
        ChatRoomService.setConnectedCallback(RTCService._startMedias)
        ChatRoomService.setUserDisconnectedCallback(RTCService._stopUserStream)
        ChatRoomService.setManagementCallback(RTCService._manageManagementMessage)
      }
    })
  }
}
