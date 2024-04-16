import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default (presences, refresher) => {
  const processable = presences.filter(presence => hasPermission('administrate', presence))

  return {
    name: 'confirm-presence',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-clipboard-check',
    label: trans('presence_validation', {}, 'presence'),
    request: {
      url: ['apiv2_cursus_event_presence_confirm'],
      request: {
        method: 'PUT',
        body: JSON.stringify(processable.map(presence => presence.id))
      },
      success: refresher.update
    },
    displayed: 0 !== processable.length,
    group: trans('validation', {}, 'presence'),
    scope: ['collection', 'object']
  }
}
