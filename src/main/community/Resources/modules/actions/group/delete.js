import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

/**
 * Delete groups action.
 */
export default (groups, refresher) => {
  const processable = groups.filter(group => !get(group, 'meta.readOnly') && hasPermission('delete', group))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    disabled: -1 === groups.findIndex(group => !get(group, 'meta.readOnly')),
    displayed: -1 !== groups.findIndex(group => hasPermission('delete', group)),
    dangerous: true,
    confirm: {
      message: transChoice('group_delete_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'community'),
      additional: trans('irreversible_action_confirm'),
      items:  processable.map(item => ({
        thumbnail: item.thumbnail,
        name: item.name
      }))
    },
    request: {
      url: url(['apiv2_group_delete'], {ids: processable.map(group => group.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
