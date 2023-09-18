import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

export default (users, refresher) => ({
  name: 'ws-register',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-book',
  label: trans('ws-register', {}, 'actions'),
  modal: [MODAL_WORKSPACES, {

    selectAction: (workspaces) => ({
      type: ASYNC_BUTTON,
      request: {
        url: ['apiv2_workspace_register'],
        request: {
          method: 'PATCH',
          body: JSON.stringify({
            workspaces: workspaces.map(workspace => workspace.id),
            users: users.map(user => user.id)
          })
        },
        success: () => refresher.update(users)
      }
    })
  }],
  group: trans('registration'),
  scope: ['object', 'collection']
})
