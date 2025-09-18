import {constants, declareAction} from '#/main/app/action'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

export default declareAction((resourceNodes) => ({
  name: 'export-evaluations',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-file-csv',
  label: trans('export_evaluations', {}, 'actions'),
  title: trans('export_evaluations', {}, 'actions'),
  labelShort: trans('export', {}, 'actions'),
  description: trans('export_resource_evaluations_help', {}, 'actions'),
  displayed: hasPermission('follow', resourceNodes[0]),
  request: {
    url: ['apiv2_resource_evaluation_csv', {resourceId: resourceNodes[0].id}]
  },
  group: trans('transfer'),
  scope: [constants.ACTION_SCOPE_OBJECT],
  set: [constants.ACTION_SET_DASHBOARD, constants.ACTION_SET_ADVANCED]
}))
