import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {isAdmin} from '#/main/app/security/permissions'

/**
 * Let the current user unregister himself from some workspaces.
 */
export default (workspaces, refresher, path, currentUser) => ({
  name: 'unregister-self',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-sign-out',
  label: trans('self-unregister', {}, 'actions'),
  // TODO : replace by workspace.permissions.unregister later
  displayed: !!currentUser && -1 !== workspaces.findIndex(workspace =>
    workspace.registered && (get(workspace, 'registration.selfUnregistration') || isAdmin(currentUser))
  ),
  request: {
    url: url(['apiv2_workspace_unregister', {user: get(currentUser, 'id')}], {workspaces: workspaces.map(workspace => workspace.id)}),
    request: {
      method: 'DELETE'
    },
    success: (response) => refresher.update(response)
  },
  dangerous: true,
  confirm: {
    title: trans('unregister'),
    message: trans('unregister_from_a_workspace')
  },
  group: trans('registration'),
  scope: ['object', 'collection']
})
