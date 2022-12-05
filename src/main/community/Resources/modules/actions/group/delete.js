import {createElement} from 'react'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

import {GroupCard} from '#/main/community/group/components/card'

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
      title: transChoice('group_delete_confirm_title', processable.length, {}, 'community'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('group_delete_confirm_message', processable.length, {count: processable.length}, 'community'),
      additional: [
        createElement('div', {
          key: 'additional',
          className: 'modal-body'
        }, processable.map(group => createElement(GroupCard, {
          key: group.id,
          orientation: 'row',
          size: 'xs',
          data: group
        })))
      ]
    },
    request: {
      url: url(['apiv2_group_delete_bulk'], {ids: processable.map(group => group.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
