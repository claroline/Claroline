import {createElement} from 'react'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {UserCard} from '#/main/community/user/components/card'

export default (users, refresher) => {
  const processable = users.filter(user => hasPermission('administrate', user) && !get(user, 'restrictions.disabled', false))

  return {
    name: 'disable',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-xmark',
    label: trans('disable', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      title: transChoice('user_disable_confirm_title', processable.length, {}, 'community'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('user_disable_confirm_message', processable.length, {count: processable.length}, 'community'),
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
      url: url(['apiv2_users_disable'], {ids: users.map(u => u.id)}),
      request: {
        method: 'PUT'
      },
      success: (response) => refresher.update(response)
    },
    scope: ['object', 'collection'],
    group: trans('management')
  }
}
