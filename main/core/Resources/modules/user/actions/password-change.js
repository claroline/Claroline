import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USER_PASSWORD} from '#/main/core/user/modals/password'

export default (users) => ({
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-lock',
  label: trans('change_password'),
  scope: ['object'],
  modal: [MODAL_USER_PASSWORD, {
    user: users[0]
  }]
})
