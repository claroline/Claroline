import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACE_PARAMETERS} from '#/main/core/workspace/modals/parameters'

export default (workspaces, workspacesRefresher) => ({
  name: 'configure',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  displayed: -1 !== workspaces.findIndex(workspace => hasPermission('administrate', workspace)),
  group: trans('management'),
  scope: ['object'],
  modal: [MODAL_WORKSPACE_PARAMETERS, {
    workspaceId: workspaces[0].id,
    onSave: (workspace) => workspacesRefresher.update([workspace])
  }]
})
