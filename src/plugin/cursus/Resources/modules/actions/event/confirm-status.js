import {declareAction} from '#/main/app/action'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default declareAction((events, refresher) => ({
  name: 'confirm-status',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-clipboard-check',
  label: trans('presence_validation', {}, 'presence'),
  displayed: hasPermission('edit', events[0]),
  request: {
    url: ['apiv2_training_event_presence_confirm', {id: events[0].id}],
    request: {
      method: 'PUT'
    },
    onSuccess: refresher.update
  },
  group: trans('management'),
  scope: ['object']
}))
