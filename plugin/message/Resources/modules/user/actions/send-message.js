import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USER_MESSAGE} from '#/main/core/user/modals'

export default (users) => ({
  type: MODAL_BUTTON,
  icon: 'fa fa-paper-plane-o',
  label: trans('send_message'),
  scope: ['object', 'collection'],
  modal: [MODAL_USER_MESSAGE, {
    to: users
  }],
  displayed: hasPermission('contact', users[0])
})
