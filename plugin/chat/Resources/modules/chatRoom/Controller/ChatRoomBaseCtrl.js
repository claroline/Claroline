/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import $ from 'jquery'
import configureTpl from '../Partial/configure.html'

export default class ChatRoomBaseCtrl {

  constructor ($state, $uibModal, ChatRoomService, FormBuilderService) {
    this.input = ''
    this.$uibModal = $uibModal
    this.$state = $state
    this.ChatRoomService = ChatRoomService
    this.FormBuilderService = FormBuilderService
    this.chatRoomConfig = ChatRoomService.getConfig()
    this.xmppConfig = ChatRoomService.getXmppConfig()
    this.messages = ChatRoomService.getMessages()
    this.oldMessages = ChatRoomService.getOldMessages()
    this.users = ChatRoomService.getUsers()
    this.bannedUsers = ChatRoomService.getBannedUsers()

    $(window).unload(($event) => {
      $event.preventDefault()
      this.ChatRoomService.disconnectFromRoom()
    })

    if (!this.chatRoomConfig['connected'] && this.chatRoomConfig['chatRoom']['room_status_text'] !== 'closed') {
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

  configure () {
    const modalInstance = this.$uibModal.open({
      template: configureTpl,
      controller: 'ChatConfigureCtrl',
      controllerAs: 'ccc',
      resolve: {
        chat: () => {
          return this.chatRoomConfig.chatRoom
          }
      }
    })

    modalInstance.result.then(result => {
      this.ChatRoomService.editChatRoom(result).then((chatRoom) => {
        this.redirect(chatRoom)
      })
    })
  }

  redirect (chatRoom) {
    if (chatRoom['room_status'] === 2) {
        this.$state.transitionTo('archive', {}, { reload: true, inherit: true, notify: true })
        return
    }

    //this.ChatRoomService.disconnectFromRoom()
    //this.ChatRoomService.connectToRoom()
    this.$state.transitionTo(chatRoom['room_type_text'], {}, { reload: true, inherit: true, notify: true })
  }
}
