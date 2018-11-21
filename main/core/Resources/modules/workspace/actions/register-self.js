import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {currentUser, isAdmin} from '#/main/app/security'

/**
 * Let the current user register himself to some workspaces.
 */
export default (workspaces, refresher) => {
  const authenticatedUser = currentUser()

  return {
    name: 'register-self',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-sign-in',
    label: trans('self-register', {}, 'actions'),
    // TODO : replace by workspace.permissions.register later
    displayed: authenticatedUser && -1 !== workspaces.findIndex(workspace =>
      !workspace.registered && !get(workspace, 'registration.waitingForRegistration') && (get(workspace, 'registration.selfRegistration') || isAdmin())
    ),
    request: {
      url: url(['apiv2_workspace_register', {user: authenticatedUser.id}], {workspaces: workspaces.map(workspace => workspace.id)}),
      request: {
        method: 'PATCH'
      },
      success: (response) => refresher.update(response)
    },
    confirm: {
      title: trans('register'),
      message: trans('register_to_a_public_workspace')
    },
    group: trans('registration'),
    scope: ['object', 'collection']
  }
}
