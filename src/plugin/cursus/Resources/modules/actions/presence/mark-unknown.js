import {createElement} from 'react'

import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {constants} from '#/plugin/cursus/constants'

export default (presences, refresher) => {
  const processable = presences.filter(presence => hasPermission('edit', presence))
  const status = constants.PRESENCE_STATUS_UNKNOWN

  return {
    name: 'mark-unknown',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-check-double',
    label: trans('presence_set_status', {}, 'cursus'),
    request: {
      url: ['apiv2_cursus_event_presence_update', {status: status}],
      request: {
        method: 'PUT',
        body: JSON.stringify(processable.map(presence => presence.id))
      },
      success: refresher.update
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
}
