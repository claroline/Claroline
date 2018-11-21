import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_GROUPS_PICKER} from '#/main/core/modals/groups'
import {MODAL_ROLES_PICKER} from '#/main/core/modals/roles'

export default (workspaces, refresher) => ({
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-users',
  label: trans('register_groups'),
  displayed: -1 !== workspaces.findIndex(workspace => hasPermission('administrate', workspace)),
  modal: [MODAL_GROUPS_PICKER, {
    title: trans('register_groups'),
    url: ['apiv2_group_list_managed'],

    // open the Roles modal after selecting groups
    selectAction: (groups) => ({
      type: MODAL_BUTTON,
      label: trans('select', {}, 'actions'),
      modal: [MODAL_ROLES_PICKER, {
        title: trans('register_groups'),
        url: ['apiv2_workspace_roles_common', {
          workspaces: workspaces.map(workspace => workspace.id)
        }],

        // send registration request for selected role and groups
        selectAction: (roles) => ({
          type: ASYNC_BUTTON,
          label: trans('register', {}, 'actions'),
          request: {
            url: ['apiv2_workspace_bulk_register_groups', {
              role: roles[0],
              workspaces: workspaces.map(workspace => workspace.id),
              groups: groups
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
