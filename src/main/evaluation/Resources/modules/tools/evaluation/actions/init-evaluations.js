import {constants, declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {constants as toolConstants} from '#/main/core/tool'
import {hasPermission} from '#/main/app/security'

export default declareAction((tools, refresher) => ({
  name: 'init-evaluations',
  type: ASYNC_BUTTON,
  label: trans('initialize_evaluations', {}, 'actions'),
  request: {
    url: ['apiv2_workspace_evaluation_init', {workspace: tools[0].contextId}],
    request: {
      method: 'PUT'
    },
    success: () => refresher.update(tools)
  },
  displayed: toolConstants.TOOL_WORKSPACE === tools[0].contextType && hasPermission('follow', tools[0]),
  score: [constants.ACTION_SCOPE_OBJECT],
  set: [constants.ACTION_SET_ADVANCED, constants.ACTION_SET_DASHBOARD],
  title: trans('initialize_evaluations', {}, 'actions'),
  description: trans('initialize_workspace_evaluations_help', {}, 'actions'),
  labelShort: trans('initialize', {}, 'actions')
}))
