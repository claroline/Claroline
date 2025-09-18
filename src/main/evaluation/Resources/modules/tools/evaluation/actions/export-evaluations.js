import {constants, declareAction} from '#/main/app/action'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {constants as toolConstants} from '#/main/core/tool'

export default declareAction((tools) => ({
  name: 'export-evaluations',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-file-csv',
  label: trans('export_evaluations', {}, 'actions'),
  title: trans('export_evaluations', {}, 'actions'),
  labelShort: trans('export', {}, 'actions'),
  description: trans('export_workspace_evaluations_help', {}, 'actions'),
  displayed: toolConstants.TOOL_WORKSPACE === tools[0].contextType && hasPermission('follow', tools[0]),
  request: {
    url: ['apiv2_workspace_evaluation_csv', {workspaceId: tools[0].contextId}]
  },
  group: trans('transfer'),
  scope: [constants.ACTION_SCOPE_OBJECT],
  set: [constants.ACTION_SET_DASHBOARD, constants.ACTION_SET_ADVANCED]
}))
