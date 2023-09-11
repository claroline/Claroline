import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_GROUPS} from '#/main/community/modals/groups'

/**
 * Registers selected groups to some workspaces.
 */
export default (workspaces, refresher) => ({
  name: 'register-groups',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-users',
  label: trans('register_groups'),
  displayed: -1 !== workspaces.findIndex(workspace => !workspace.meta.model && !workspace.meta.archived && hasPermission('administrate', workspace)),
  modal: [MODAL_GROUPS, {
    title: trans('register_groups'),

    // load the list of common roles for selected workspaces
    selectAction: (groups) => ({
      type: ASYNC_BUTTON,
      request: {
        url: ['apiv2_workspace_bulk_register_groups', {
          workspaces: workspaces.map(workspace => workspace.id),
          groups: groups.map(group => group.id)
        }],
        request: {
          method: 'PATCH'
        },
        success: () => refresher.update(workspaces)
      }
    })
  }],
  group: trans('registration'),
  scope: ['object', 'collection']
})
