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
import closeRoomTpl from '../Partial/close.html'
//import changeRoomTypeTpl from '../Partial/changeRoomType.html'
import ChatRoom from '../Model/ChatRoom'

export default class ChatRoomBaseCtrl {

  constructor ($state, $uibModal, $rootScope, ChatRoomService, FormBuilderService) {
    this.input = ''
    this.$uibModal = $uibModal
    this.$state = $state
    this.$rootScope = $rootScope
    this.ChatRoomService = ChatRoomService
    this.FormBuilderService = FormBuilderService
    this.chatRoomConfig = ChatRoomService.getConfig()
    this.xmppConfig = ChatRoomService.getXmppConfig()
    this.messages = ChatRoomService.getMessages()
    this.oldMessages = ChatRoomService.getOldMessages()
    this.users = ChatRoomService.getUsers()
    this.bannedUsers = ChatRoomService.getBannedUsers()

    // the config must change to request approriate medias
    $rootScope.$on('$stateChangeStart', (event, toState) => {
      if (toState.name === 'text') {
        ChatRoomService.setConnectedCallback(() => {
        })
        ChatRoomService.setUserDisconnectedCallback(() => {
        })
        ChatRoomService.setManagementCallback(() => {
        })
      }
    })

    ChatRoomService.setCloseCallback(() => {
      this._closeRoomCallback()
    })

    ChatRoomService.setChangeRoomTypeCallback((type) => {
      this._changeRoomTypeCallback(type)
    })

    $(window).unload(($event) => {
      $event.preventDefault()
      this.ChatRoomService.disconnectFromRoom()
    })

    if (!this.chatRoomConfig['connected'] && this.chatRoomConfig['chatRoom']['room_status_text'] !== 'closed') {
      this.ChatRoomService.connectToRoom()
    }
  }

  _changeRoomTypeCallback () {
    this.goBack()
  /*this.$uibModal.open({template: changeRoomTypeTpl}).result.then(() => {

  })*/
  }

  _closeRoomCallback () {
    this.$uibModal.open({template: closeRoomTpl}).result.then(() => {
      this.$state.transitionTo('archive', {}, { reload: true, inherit: true, notify: true })
    })
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
    if (chatRoom['room_status'] === ChatRoom.CLOSED) {
      this.ChatRoomService.close()
    }

    this.ChatRoomService.changeRoomType(chatRoom['room_type_text'])
  }
}
