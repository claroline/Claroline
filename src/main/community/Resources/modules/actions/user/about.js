import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USER_ABOUT} from '#/main/community/user/modals/about'

export default (users) => ({
  name: 'about',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-circle-info',
  label: trans('show-info', {}, 'actions'),
  displayed: hasPermission('open', users[0]),
  modal: [MODAL_USER_ABOUT, {
    userId: users[0].id
  }],
  scope: ['object']
})
