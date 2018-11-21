import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USERS_PICKER} from '#/main/core/modals/users'
import {MODAL_ROLES_PICKER} from '#/main/core/modals/roles'

/**
 * Registers selected users to some workspaces.
 */
export default (workspaces, refresher) => ({
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-user',
  label: trans('register_users'),
  displayed: -1 !== workspaces.findIndex(workspace => hasPermission('administrate', workspace)),
  modal: [MODAL_USERS_PICKER, {
    title: trans('register_users'),
    url: ['apiv2_user_list_managed_organization'],

    // open the Roles modal after selecting users
    selectAction: (users) => ({
      type: MODAL_BUTTON,
      label: trans('select', {}, 'actions'),
      modal: [MODAL_ROLES_PICKER, {
        title: trans('register_users'),
        url: ['apiv2_workspace_roles_common', {
          workspaces: workspaces.map(workspace => workspace.id)
        }],

        // send registration request for selected role and users
        selectAction: (roles) => ({
          type: ASYNC_BUTTON,
          label: trans('register', {}, 'actions'),
          request: {
            url: ['apiv2_workspace_bulk_register_users', {
              role: roles[0],
              workspaces: workspaces.map(workspace => workspace.id),
              users: users
            }],
            request: {
              method: 'PATCH'
            },
            success: () => refresher.update(workspaces)
          }
        })
      }]
    })
  }],
  group: trans('registration'),
  scope: ['object', 'collection']
})
