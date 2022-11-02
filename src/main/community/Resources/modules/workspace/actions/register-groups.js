import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {MODAL_GROUPS} from '#/main/community/modals/groups'
import {MODAL_WORKSPACE_ROLES} from '#/main/core/workspace/modals/roles'

/**
 * Registers selected groups to some workspaces.
 */
export default (workspaces, refresher) => ({
  name: 'register-groups',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-users',
  label: trans('register_groups'),
  displayed: -1 !== workspaces.findIndex(workspace => !workspace.meta.archived && hasPermission('administrate', workspace)),
  // open a modal to select the list of groups to register
  modal: [MODAL_GROUPS, {
    title: trans('register_groups'),

    // load the list of common roles for selected workspaces
    selectAction: (groups) => ({
      type: ASYNC_BUTTON,
      request: {
        url: ['apiv2_workspace_roles_common', {
          workspaces: workspaces.map(workspace => workspace.id)
        }],
        success: (response, dispatch) => dispatch(modalActions.showModal(MODAL_WORKSPACE_ROLES, {
          icon: 'fa fa-fw fa-users',
          title: trans('register_groups'),
          roles: response,
          // send registration request for selected role and groups
          selectAction: (role) => ({
            type: ASYNC_BUTTON,
            label: trans('register', {}, 'actions'),
            request: {
              url: ['apiv2_workspace_bulk_register_groups', {
                role: role.translationKey,
                workspaces: workspaces.map(workspace => workspace.id),
                groups: groups.map(group => group.id)
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
