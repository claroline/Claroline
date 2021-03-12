import {hasPermission} from '#/main/app/security'
import {param} from '#/main/app/config'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USER_PASSWORD} from '#/main/core/user/modals/password'

export default (users) => ({
  name: 'password-change',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-lock',
  label: trans('change_password'),
  scope: ['object'],
  displayed: hasPermission('administrate', users[0]) || (param('authentication.changePassword') && hasPermission('edit', users[0])),
  modal: [MODAL_USER_PASSWORD, {
    user: users[0]
  }],
  group: trans('management')
})
