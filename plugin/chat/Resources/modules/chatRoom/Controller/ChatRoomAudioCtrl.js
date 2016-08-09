/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import $ from 'jquery'

import ChatRoomVideoCtrl from './ChatRoomVideoCtrl'

export default class ChatRoomAudioCtrl extends ChatRoomVideoCtrl {
    constructor ($state, $uibModal, $log, ChatRoomService, VideoService, FormBuilderService) {
        super($state, $uibModal, $log, ChatRoomService, VideoService, FormBuilderService)
        this.VideoService.getConfig().myVideoEnabled = false
    }
}
