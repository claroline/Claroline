import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACE_IMPERSONATION} from '#/main/core/workspace/modals/impersonation'

export default (workspaces) => ({
  name: 'impersonation',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-user-secret',
  label: trans('view-as', {}, 'actions'),
  displayed: hasPermission('administrate', workspaces[0]),
  modal: [MODAL_WORKSPACE_IMPERSONATION, {
    workspace: workspaces[0]
  }],
  group: trans('management'),
  scope: ['object']
})
