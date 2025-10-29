import {declareAction} from '#/main/app/action'
import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default declareAction((events) => ({
  name: 'export-presences-filled',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-border-all',
  label: trans('export-presences-filled', {}, 'cursus'),
  displayed: hasPermission('edit', events[0]),
  target: ['apiv2_training_event_presence_download', {id: events[0].id, filled: 1}],
  group: trans('transfer'),
  scope: ['object']
}))
