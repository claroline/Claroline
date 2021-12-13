import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {isAdmin} from '#/main/app/security/permissions'

/**
 * Let the current user register himself to some workspaces.
 */
export default (workspaces, refresher, path, currentUser) => ({
  name: 'register-self',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-sign-in',
  label: trans('self_register', {}, 'actions'),
  // TODO : replace by workspace.permissions.register later
  displayed: !!currentUser && -1 !== workspaces.findIndex(workspace =>
    !workspace.registered && !workspace.meta.archived && !get(workspace, 'registration.waitingForRegistration') && (get(workspace, 'registration.selfRegistration') || isAdmin(currentUser))
  ),
  request: {
    url: url(['apiv2_workspace_register', {user: get(currentUser, 'id')}], {workspaces: workspaces.map(workspace => workspace.id)}),
    request: {
      method: 'PATCH'
    },
    success: (response) => refresher.update(response)
  },
  confirm: {
    title: trans('register'),
    message: trans('register_confirm_message')
  },
  group: trans('registration'),
  scope: ['object', 'collection'],
  primary: true
})
