/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import configureTpl from '../Partial/configure.html'

export default class ChatRoomArchiveCtrl {
  constructor (ChatRoomService, FormBuilderService, $uibModal, $state) {
    this.FormBuilderService = FormBuilderService
    this.$uibModal = $uibModal
    this.$state = $state
    this.ChatRoomService = ChatRoomService
    this.chatRoomConfig = ChatRoomService.getConfig()
    this.oldMessages = ChatRoomService.getRegisteredMessages()
  }

  configure () {
    const modalInstance = this.$uibModal.open({
      template: configureTpl,
      controller: 'ChatConfigureCtrl',
      controllerAs: 'ccc',
      resolve: {
        chat: () => {
          return this.chatRoomConfig.chat}
      }
    })

    modalInstance.result.then(result => {
      if (!result) return
      var data = this.FormBuilderService.submit(
        Routing.generate('api_put_chat_room', {chatRoom: result.id}),
        {'chat_room': result},
        'PUT'
      ).then(
        d => {
            alert('toto')

        },
        d => alert('error')
      )
    })
  }
}
