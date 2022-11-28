import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {isAdmin} from '#/main/app/security/permissions'
import {MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {MODAL_USERS} from '#/main/community/modals/users'

export default (user) => ({
  name: 'view-as',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-mask',
  label: trans('view-as', {}, 'actions'),
  displayed: user && isAdmin(user),
  modal: [MODAL_USERS, {
    selectAction: (users) => ({
      type: URL_BUTTON,
      target: !isEmpty(users) ? url(['claro_index', {_switch: users[0].username}])+'#/desktop' : ''
    })
  }],
  group: trans('management')
})
