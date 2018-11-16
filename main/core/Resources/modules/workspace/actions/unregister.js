import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {currentUser, isAdmin} from '#/main/core/user/current'

/**
 * Unregister from workspaces action.
 */
export default (workspaces, refresher) => {
  const authenticatedUser = currentUser()

  return {
    name: 'unregister',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-arrow-from-left',
    label: trans('unregister', {}, 'actions'),
    // TODO : replace by workspace.permissions.unregister later
    displayed: authenticatedUser && -1 !== workspaces.findIndex(workspace =>
      workspace.registered && (get(workspace, 'registration.selfUnregistration') || isAdmin())
    ),
    request: {
      url: url(['apiv2_workspace_unregister', {user: authenticatedUser.id}], {workspaces: workspaces.map(workspace => workspace.id)}),
      request: {
        method: 'DELETE'
      },
      success: (response) => refresher.update(response)
    },
    dangerous: true,
    confirm: {
      title: trans('unregister'),
      message: trans('unregister_from_a_workspace')
    }
  }
}
