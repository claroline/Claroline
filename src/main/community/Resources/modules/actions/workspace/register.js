import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_REGISTER} from '#/main/community/actions/workspace/modals/register'

/**
 * Registers selected users groups to some workspaces.
 */
export default (workspaces, refresher) => ({
  name: 'register-users-groups',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-users',
  label: trans('register_users_groups', {}, 'platform'),
  displayed: -1 !== workspaces.findIndex(workspace => !workspace.meta.model && !workspace.meta.archived && hasPermission('administrate', workspace)),
  modal: [MODAL_REGISTER, {
    title: trans('register_users_groups'),

    selectAction: (groups, users) => ({
      type: ASYNC_BUTTON,
      request: {
        url: ['apiv2_workspace_register'],
        request: {
          method: 'PATCH',
          body: JSON.stringify({
            workspaces: workspaces.map(workspace => workspace.id),
            groups: groups.map(group => group.id),
            users: users.map(user => user.id)
          })
        },
        success: () => refresher.update(workspaces)
      }
    })
  }],
  group: trans('registration'),
  scope: ['object', 'collection']
})
