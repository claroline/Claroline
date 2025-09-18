import {constants, declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {constants as toolConstants} from '#/main/core/tool'
import {hasPermission} from '#/main/app/security'

export default declareAction((tools, refresher) => ({
  name: 'purge-evaluations',
  label: trans('purge_evaluations', {}, 'actions'),
  type: ASYNC_BUTTON,
  confirm: {
    message: trans('purge_workspace_evaluations_confirm', {}, 'actions'),
    additional: trans('irreversible_action_confirm')
  },
  request: {
    url: ['apiv2_workspace_evaluation_purge', {workspaceId: tools[0].contextId}],
    request: {
      method: 'DELETE'
    },
    success: () => refresher.update(tools)
  },
  dangerous: true,
  displayed: toolConstants.TOOL_WORKSPACE === tools[0].contextType && hasPermission('administrate', tools[0]),
  score: ['object'],
  set: [constants.ACTION_SET_ADVANCED, constants.ACTION_SET_DASHBOARD],
  title: trans('purge_evaluations', {}, 'actions'),
  description: trans('purge_workspace_evaluations_help', {}, 'actions'),
  labelShort: trans('purge', {}, 'actions'),
  managerOnly: true
}))
