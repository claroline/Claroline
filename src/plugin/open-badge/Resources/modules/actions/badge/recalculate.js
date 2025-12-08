import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {ASYNC_BUTTON} from '#/main/app/buttons'
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {constants, declareAction} from '#/main/app/action'

export default declareAction((badges, refresher) => ({
  name: 'recalculate',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-refresh',
  label: trans('recalculate', {}, 'actions'),
  displayed: hasPermission('follow', badges[0]) && !isEmpty(get(badges[0], 'rules')) && !get(badges[0], 'meta.archived', false),
  request: {
    url: url(['apiv2_badge_recalculate', {badge: badges[0].id}]),
    request: {
      method: 'POST'
    },
    success: () => refresher.update(badges)
  },
  scope: ['object'],
  group: trans('management'),
  set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS, constants.ACTION_SET_ADVANCED],
  title: trans('recompute_assertions', {}, 'actions'),
  description: trans('recompute_assertions_desc', {}, 'actions')
}))
