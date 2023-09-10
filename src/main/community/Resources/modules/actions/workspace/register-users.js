import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USERS} from '#/main/community/modals/users'

/**
 * Registers selected users to some workspaces.
 */
export default (workspaces, refresher) => ({
  name: 'register-users',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-user',
  label: trans('register_users'),
  displayed: -1 !== workspaces.findIndex(workspace => !workspace.meta.model && !workspace.meta.archived && hasPermission('administrate', workspace)),
  modal: [MODAL_USERS, {
    title: trans('register_users'),
    selectAction: (users) => ({
      type: ASYNC_BUTTON,
      request: {
        url: ['apiv2_workspace_bulk_register_users', {
          workspaces: workspaces.map(workspace => workspace.id),
          users: users.map(user => user.id)
        }],
        request: {
          method: 'PATCH'
        },
        success: () => refresher.update(workspaces)
      }
    })
  }],
  group: trans('registration'),
  scope: ['object', 'collection']
})
