import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'
import {isAdmin} from '#/main/app/security/permissions'

/**
 * Let the current user register himself to some workspaces.
 */
export default (workspaces, refresher, path, currentUser) => {
  const processable = workspaces.filter(workspace => !workspace.registered && !get(workspace, 'meta.model') && !get(workspace, 'meta.archived') && !get(workspace, 'registration.waitingForRegistration')
    && (get(workspace, 'registration.selfRegistration') || isAdmin(currentUser)))

  return {
    name: 'register-self',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-sign-in',
    label: trans('self_register', {}, 'actions'),
    displayed: !!currentUser && 0 !== processable.length,
    confirm: {
      message: transChoice('self_register_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'workspace'),
      items:  processable.map(item => ({
        thumbnail: item.thumbnail,
        name: item.name
      }))
    },
    request: {
      url: url(['apiv2_workspace_self_register', {workspace: processable.length > 0 ? processable[0].id : undefined}]),
      request: {
        method: 'PATCH'
      },
      success: (response) => refresher.update(response)
    },
    group: trans('registration'),
    scope: ['object', 'collection'],
    primary: true
  }
}
