import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_MESSAGE} from '#/plugin/message/modals/message'

export default (users) => ({
  type: MODAL_BUTTON,
  icon: 'fa fa-paper-plane',
  label: trans('send-message', {}, 'actions'),
  scope: ['object', 'collection'],
  modal: [MODAL_MESSAGE, {
    to: users
  }],
  displayed: hasPermission('contact', users[0])
})
