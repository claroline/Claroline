import {createElement} from 'react'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {ASYNC_BUTTON} from '#/main/app/buttons'
import {url} from '#/main/app/api'
import {trans, transChoice} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {BadgeCard} from '#/plugin/open-badge/badge/components/card'

export default (badges, refresher) => {
  const processable = badges.filter(badge => hasPermission('edit', badge) && get(badge, 'meta.enabled', false))

  return {
    name: 'disable',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-circle-xmark',
    label: trans('disable', {}, 'actions'),

    displayed: !isEmpty(processable),
    request: {
      url: url(['apiv2_badge-class_disable'], {ids: processable.map(u => u.id)}),
      request: {
        method: 'PUT'
      },
      success: () => refresher.update(processable)
    },
    confirm: {
      title: transChoice('badge_disable_confirm_title', processable.length, {}, 'badge'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('badge_disable_confirm_message', processable.length, {count: processable.length}, 'badge'),
      additional: [
        createElement('div', {
          key: 'additional',
          className: 'modal-body'
        }, processable.map(badge => createElement(BadgeCard, {
          key: badge.id,
          orientation: 'row',
          size: 'xs',
          data: badge
        })))
      ]
    },
    scope: ['object', 'collection'],
    group: trans('management')
  }
}
