import {declareAction} from '#/main/app/action'
import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default declareAction((events) => ({
  name: 'export-presences-empty',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-border-none',
  label: trans('export-presences-empty', {}, 'cursus'),
  displayed: hasPermission('edit', events[0]),
  target: ['apiv2_training_event_presence_download', {id: events[0].id, filled: 0}],
  group: trans('transfer'),
  scope: ['object']
}))
