import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'
import {isAdmin} from '#/main/app/security/permissions'

/**
 * Let the current user unregister himself from some workspaces.
 */
export default (workspaces, refresher, path, currentUser) => {
  const unregisterFrom = workspaces.filter(workspace => !!currentUser &&
    workspace.registered && (get(workspace, 'registration.selfUnregistration') || isAdmin(currentUser)))

  return ({
    name: 'unregister-self',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-sign-out',
    label: trans('self-unregister', {}, 'actions'),
    displayed: 0 !== unregisterFrom.length,
    request: {
      url: url(['apiv2_workspace_unregister', {user: get(currentUser, 'id')}], {workspaces: workspaces.map(workspace => workspace.id)}),
      request: {
        method: 'DELETE'
      },
      success: (response) => refresher.update(response)
    },
    dangerous: true,
    confirm: {
      title: trans('unregister_confirm_title'),
      message: transChoice('unregister_workspaces_confirm_message', unregisterFrom.length, {count: unregisterFrom.length})
    },
    group: trans('registration'),
    scope: ['object', 'collection']
  })
}
