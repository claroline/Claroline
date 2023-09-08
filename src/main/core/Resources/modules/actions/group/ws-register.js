import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

export default (groups, refresher) => ({
  name: 'ws-register',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-book',
  label: trans('ws-register', {}, 'actions'),
  modal: [MODAL_WORKSPACES, {

    selectAction: (workspaces) => ({
      type: ASYNC_BUTTON,
      request: {
        url: ['apiv2_workspace_bulk_register_groups', {
          workspaces: workspaces.map(workspace => workspace.id),
          groups: groups.map(groups => groups.id)
        }],
        request: {
          method: 'PATCH'
        },
        success: () => refresher.update(groups)
      }
    })
  }],
  group: trans('registration'),
  scope: ['object', 'collection']
})
