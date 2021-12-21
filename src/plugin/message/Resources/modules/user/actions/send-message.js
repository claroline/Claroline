import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_MESSAGE} from '#/plugin/message/modals/message'

export default (users, usersRefresher, path, currentUser) => {
  const filteredUsers = users.filter(user => !currentUser || user.id !== currentUser.id)

  return {
    type: MODAL_BUTTON,
    icon: 'fa fa-fw fa-paper-plane',
    label: trans('send-message', {}, 'actions'),
    scope: ['object', 'collection'],
    modal: [MODAL_MESSAGE, {
      receivers: {
        users: filteredUsers
      }
    }],
    displayed: -1 !== filteredUsers.findIndex(user => hasPermission('contact', user)),
    group: trans('community')
  }
}
