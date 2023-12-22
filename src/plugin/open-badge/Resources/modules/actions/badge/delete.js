import {createElement} from 'react'
import isEmpty from 'lodash/isEmpty'

import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

import {BadgeCard} from '#/plugin/open-badge/badge/components/card'

/**
 * Delete badges action.
 */
export default (badges, refresher) => {
  const processable = badges.filter(badge => hasPermission('delete', badge))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: !isEmpty(processable),
    dangerous: true,
    confirm: {
      title: transChoice('badge_delete_confirm_title', processable.length, {}, 'badge'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('badge_delete_confirm_message', processable.length, {count: processable.length}, 'badge'),
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
    request: {
      url: url(['apiv2_badge-class_delete_bulk'], {ids: processable.map(badge => badge.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
