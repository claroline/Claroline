import {declareAction} from '#/main/app/action'
import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default declareAction((events) => ({
  name: 'export-pdf',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-file-pdf',
  label: trans('export-pdf', {}, 'actions'),
  displayed: hasPermission('open', events[0]),
  target: ['apiv2_training_event_download_pdf', {id: events[0].id}],
  scope: ['object'],
  group: trans('transfer')
}))
