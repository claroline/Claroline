/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import mainTpl from './Partial/main.html'
import textTpl from './Partial/roomText.html'
import videoTpl from './Partial/roomVideo.html'
import audioTpl from './Partial/roomAudio.html'

export default function($stateProvider, $urlRouterProvider) {
  $stateProvider
    .state('main', {
      url: '/main',
      template: mainTpl,
      controller: 'ChatRoomInitCtrl',
      controllerAs: 'crmc'
    })
    .state('text', {
      url: '/text',
      template: textTpl,
      controller: 'ChatRoomTextCtrl',
      controllerAs: 'crc'
    })
    .state('video', {
      url: '/video',
      template: videoTpl,
      controller: 'ChatRoomVideoCtrl',
      controllerAs: 'crc'
    })
    .state('audio', {
      url: '/audio',
      template: audioTpl,
      controller: 'ChatRoomAudioCtrl',
      controllerAs: 'crc'
    })

  $urlRouterProvider.otherwise('/main')
}
