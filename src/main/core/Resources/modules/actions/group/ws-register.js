import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

export default (groups, refresher) => {
  const processable = groups.filter(group => hasPermission('administrate', group))

  return {
    name: 'ws-register',
    type: MODAL_BUTTON,
    icon: 'fa fa-fw fa-book',
    label: trans('ws-register', {}, 'actions'),
    displayed: 0 !== processable.length,
    modal: [MODAL_WORKSPACES, {
      url: ['apiv2_workspace_list'],
      selectAction: (workspaces) => ({
        type: ASYNC_BUTTON,
        request: {
          url: ['apiv2_workspace_register'],
          request: {
            method: 'PATCH',
            body: JSON.stringify({
              workspaces: workspaces.map(workspace => workspace.id),
              groups: processable.map(group => group.id)
            })
          },
          success: () => refresher.update(processable)
        }
      })
    }],
    group: trans('registration'),
    scope: ['object', 'collection']
  }
}
