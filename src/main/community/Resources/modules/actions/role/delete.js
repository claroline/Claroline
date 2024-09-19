import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

/**
 * Delete roles action.
 */
export default (roles, refresher) => {
  const processable = roles.filter(role => !get(role, 'meta.readOnly') && -1 === role.name.indexOf('ROLE_WS_COLLABORATOR_') && -1 === role.name.indexOf('ROLE_WS_MANAGER_') && hasPermission('delete', role))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    disabled: -1 === roles.findIndex(role => !get(role, 'meta.readOnly') && -1 === role.name.indexOf('ROLE_WS_COLLABORATOR_') && -1 === role.name.indexOf('ROLE_WS_MANAGER_')),
    displayed: -1 !== roles.findIndex(role => hasPermission('delete', role)),
    dangerous: true,
    confirm: {
      message: transChoice('role_delete_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'community'),
      additional: trans('irreversible_action_confirm'),
      items:  processable.map(item => ({
        thumbnail: item.thumbnail,
        name: item.name
      }))
    },
    request: {
      url: url(['apiv2_role_delete'], {ids: processable.map(role => role.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
