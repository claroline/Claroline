import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {ASYNC_BUTTON} from '#/main/app/buttons'
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default (badges, refresher) => {
  const processable = badges.filter(badge => hasPermission('edit', badge) && !get(badge, 'meta.enabled'))

  return {
    name: 'enable',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-circle-check',
    label: trans('enable', {}, 'actions'),

    displayed: !isEmpty(processable),
    request: {
      url: url(['apiv2_badge-class_enable'], {ids: processable.map(u => u.id)}),
      request: {
        method: 'PUT'
      },
      success: () => refresher.update(processable)
    },
    scope: ['object', 'collection'],
    group: trans('management')
  }
}
