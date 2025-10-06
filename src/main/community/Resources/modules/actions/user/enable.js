import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {constants, declareAction} from '#/main/app/action'

export default declareAction((users, refresher) => {
  const processable = users.filter(user => hasPermission('administrate', user) && get(user, 'restrictions.disabled', false))

  return {
    name: 'enable',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-user-check',
    label: trans('enable', {}, 'actions'),
    scope: ['object', 'collection'],
    displayed: 0 !== processable.length,
    request: {
      url: ['apiv2_user_enable'],
      request: {
        method: 'PUT',
        body: JSON.stringify(processable.map(u => u.id))
      },
      success: refresher.update
    },
    group: trans('management'),
    set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS, constants.ACTION_SET_ADVANCED],
    managerOnly: true,
    title: trans('enable_user', {}, 'actions'),
    description: trans('enable_user_desc', {}, 'actions')
  }
})
