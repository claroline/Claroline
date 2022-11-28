import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACE_ABOUT} from '#/main/core/workspace/modals/about'

export default (workspaces) => ({
  name: 'about',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-info',
  label: trans('show-info', {}, 'actions'),
  displayed: hasPermission('open', workspaces[0]),
  modal: [MODAL_WORKSPACE_ABOUT, {
    workspaceId: workspaces[0].id
  }],
  scope: ['object']
})
