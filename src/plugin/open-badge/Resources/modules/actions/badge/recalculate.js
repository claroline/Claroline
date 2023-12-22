import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {ASYNC_BUTTON} from '#/main/app/buttons'
import {API_REQUEST, url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default (badges, refresher) => ({
  name: 'recalculate',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-circle-check',
  label: trans('recalculate', {}, 'actions'),
  displayed: hasPermission('grant', badges[0]) && !isEmpty(get(badges[0], 'rules')) && !get(badges[0], 'meta.enabled'),
  request: {
    url: url(['apiv2_badge-class_recalculate_users', {badge: badges[0].id}]),
    request: {
      method: 'POST'
    },
    success: () => refresher.update(badges)
  },
  scope: ['object'],
  group: trans('management')
})
