import {declareAction} from '#/main/app/action'
import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default declareAction((events) => ({
  name: 'export-ics',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-calendar',
  label: trans('export-ics', {}, 'actions'),
  displayed: hasPermission('open', events[0]),
  target: ['apiv2_training_event_download_ics', {id: events[0].id}],
  scope: ['object'],
  group: trans('transfer')
}))
