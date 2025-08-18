import {createElement} from 'react'

import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {constants} from '#/plugin/cursus/constants'
import {declareAction} from '#/main/app/action'
import get from 'lodash/get'

export default declareAction((registrations, refresher) => {
  const processable = registrations.filter(registration => constants.TEACHER_TYPE !== registration.type && hasPermission('edit', registration))
  const status = constants.PRESENCE_STATUS_ABSENT_JUSTIFIED

  return {
    name: 'mark-absent-justified',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-check-double',
    label: trans('presence_set_status', {}, 'cursus'),
    request: {
      url: ['apiv2_cursus_event_presence_update', {status: status}],
      request: {
        method: 'PUT',
        body: JSON.stringify(processable.map(registration => get(registration.presence, 'id')))
      },
      success: () => refresher.update(processable)
    },
    displayed: 0 !== processable.length,
    primary: true,
    group: trans('management'),
    scope: ['collection', 'object'],
    children: createElement('b', {
      className: `ms-2 fw-semibold text-${constants.PRESENCE_STATUS_COLORS[status]}`,
      children: constants.PRESENCE_STATUSES[status]
    })
  }
})
