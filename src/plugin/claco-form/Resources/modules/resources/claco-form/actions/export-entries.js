import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {constants, declareAction} from '#/main/app/action'
import {hasPermission} from '#/main/app/security'

export default declareAction((resourceNodes) => ({
  name: 'export-entries',
  type: URL_BUTTON,
  icon: 'fa fa-fw fa-file-archive',
  label: trans('export_all_entries', {}, 'clacoform'),
  target: ['claro_claco_form_entries_export', {id: resourceNodes[0].id}],
  group: trans('transfer'),
  displayed: hasPermission('follow', resourceNodes[0]),
  scope: [constants.ACTION_SCOPE_OBJECT],
  set: [constants.ACTION_SET_DETAILS, constants.ACTION_SET_DASHBOARD]
}))
