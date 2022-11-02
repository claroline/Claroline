import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {MODAL_ROLES} from '#/main/community/modals/roles'

import {route} from '#/main/core/workspace/routing'

export default (workspaces) => ({
  name: 'impersonation',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-mask',
  label: trans('view-as', {}, 'actions'),
  displayed: hasPermission('administrate', workspaces[0]),
  modal: [MODAL_ROLES, {
    url: ['apiv2_workspace_list_roles_configurable', {workspace: workspaces[0].id}],
    filters: [],
    icon: 'fa fa-fw fa-mask',
    title: trans('view-as', {}, 'actions'),
    subtitle: workspaces[0].name,
    selectAction: (selected) => ({
      type: URL_BUTTON,
      label: trans('view-as', {}, 'actions'),
      target: !isEmpty(selected) ? url(['claro_index', {}], {view_as: selected[0].name}) + '#' + route(workspaces[0]) : ''
    })
  }],
  group: trans('management'),
  scope: ['object']
})
