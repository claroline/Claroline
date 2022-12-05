import {createElement} from 'react'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

import {UserCard} from '#/main/community/user/components/card'

/**
 * Delete users action.
 */
export default (users, refresher) => {
  const processable = users.filter(user => hasPermission('delete', user) && get(user, 'restrictions.disabled'))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      title: transChoice('user_delete_confirm_title', processable.length, {}, 'community'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('user_delete_confirm_message', processable.length, {count: processable.length}, 'community'),
      additional: [
        createElement('div', {
          key: 'additional',
          className: 'modal-body'
        }, processable.map(user => createElement(UserCard, {
          key: user.id,
          orientation: 'row',
          size: 'xs',
          data: user
        })))
      ]
    },
    request: {
      url: url(['apiv2_user_delete_bulk'], {ids: processable.map(user=> user.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
