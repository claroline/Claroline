import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {URL_BUTTON, ASYNC_BUTTON} from '#/main/app/buttons'

import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {route} from '#/main/core/workspace/routing'
import {MODAL_WORKSPACE_ROLES} from '#/main/core/workspace/modals/roles'

export default (workspaces) => ({
  name: 'impersonation',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-mask',
  label: trans('view-as', {}, 'actions'),
  displayed: hasPermission('administrate', workspaces[0]),
  // load the list of Workspace roles (may not be loaded when action is rendered in a list)
  request: {
    url: ['apiv2_workspace_list_roles_configurable', {id: workspaces[0].uuid}],
    // open the roles modal to let the user choose one
    success: (response, dispatch) => dispatch(modalActions.showModal(MODAL_WORKSPACE_ROLES, {
      icon: 'fa fa-fw fa-mask',
      title: trans('view-as', {}, 'actions'),
      subtitle: workspaces[0].name,
      roles: response.data,
      // open the workspace with the selected role
      selectAction: (role) => ({
        type: URL_BUTTON,
        label: trans('view-as', {}, 'actions'),
        target: url(['claro_index', {}], {view_as: role.name}) + '#' + route(workspaces[0])
      })
    }))
  },
  group: trans('management'),
  scope: ['object']
})
