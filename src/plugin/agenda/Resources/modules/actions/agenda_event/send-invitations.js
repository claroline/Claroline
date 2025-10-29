import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {declareAction} from '#/main/app/action'

export default declareAction((events) => {
  return {
    name: 'send-invitation',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-envelope',
    label: trans('send_invitations', {}, 'actions'),
    displayed: hasPermission('edit', events[0]),
    confirm: trans('event_send_invitations_confirm', {}, 'agenda'),
    request: {
      url: ['apiv2_event_send_invitations', {id: events[0].id}],
      request: {
        method: 'POST'
      }
    },
    group: trans('management'),
    scope: ['object']
  }
})
