import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USER_PASSWORD} from '#/main/core/user/modals/password'

export default (users, refresher, path, currentUser) => ({
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-lock',
  label: trans('change_password'),
  scope: ['object'],
  displayed: hasPermission('administrate', users[0]) || users[0].id === get(currentUser, 'id'),
  modal: [MODAL_USER_PASSWORD, {
    user: users[0]
  }]
})
