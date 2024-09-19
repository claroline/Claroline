import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {ASYNC_BUTTON} from '#/main/app/buttons'
import {url} from '#/main/app/api'
import {trans, transChoice} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default (badges, refresher) => {
  const processable = badges.filter(badge => hasPermission('edit', badge) && get(badge, 'meta.enabled', false))

  return {
    name: 'disable',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-circle-xmark',
    label: trans('disable', {}, 'actions'),

    displayed: !isEmpty(processable),
    request: {
      url: url(['apiv2_badge_disable'], {ids: processable.map(u => u.id)}),
      request: {
        method: 'PUT'
      },
      success: () => refresher.update(processable)
    },
    confirm: {
      message: transChoice('badge_disable_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'badge'),
      items:  processable.map(item => ({
        thumbnail: item.thumbnail,
        name: item.name
      }))
    },
    scope: ['object', 'collection'],
    group: trans('management')
  }
}
