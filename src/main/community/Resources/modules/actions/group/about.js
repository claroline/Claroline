import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_GROUP_ABOUT} from '#/main/community/group/modals/about'

export default (groups) => ({
  name: 'about',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-circle-info',
  label: trans('show-info', {}, 'actions'),
  displayed: hasPermission('open', groups[0]),
  modal: [MODAL_GROUP_ABOUT, {
    groupId: groups[0].id
  }],
  scope: ['object']
})
