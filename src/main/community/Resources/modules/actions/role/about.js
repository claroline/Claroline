import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_ROLE_ABOUT} from '#/main/community/role/modals/about'

export default (roles) => ({
  name: 'about',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-circle-info',
  label: trans('show-info', {}, 'actions'),
  displayed: hasPermission('open', roles[0]),
  modal: [MODAL_ROLE_ABOUT, {
    roleId: roles[0].id
  }],
  scope: ['object']
})
