import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {MODAL_USERS} from '#/main/community/modals/users'
import {MODAL_WORKSPACE_ROLES} from '#/main/core/workspace/modals/roles'

/**
 * Registers selected users to some workspaces.
 */
export default (workspaces, refresher) => ({
  name: 'register-users',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-user',
  label: trans('register_users'),
  displayed: -1 !== workspaces.findIndex(workspace => !workspace.meta.archived && hasPermission('administrate', workspace)),
  // open a modal to select the list of users to register
  modal: [MODAL_USERS, {
    title: trans('register_users'),

    // load the list of common roles for selected workspaces
    selectAction: (users) => ({
      type: ASYNC_BUTTON,
      request: {
        url: ['apiv2_workspace_roles_common', {
          workspaces: workspaces.map(workspace => workspace.id)
        }],
        success: (response, dispatch) => dispatch(modalActions.showModal(MODAL_WORKSPACE_ROLES, {
          icon: 'fa fa-fw fa-user',
          title: trans('register_users'),
          roles: response,
          // send registration request for selected role and users
          selectAction: (role) => ({
            type: ASYNC_BUTTON,
            label: trans('register', {}, 'actions'),
            request: {
              url: ['apiv2_workspace_bulk_register_users', {
                role: role.translationKey,
                workspaces: workspaces.map(workspace => workspace.id),
                users: users.map(user => user.id)
              }],
              request: {
                method: 'PATCH'
              },
              success: () => refresher.update(workspaces)
            }
          })
        }))
      }
    })
  }],
  group: trans('registration'),
  scope: ['object', 'collection']
})
