import {constants, declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {constants as toolConstants} from '#/main/core/tool'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

export default declareAction((tools) => ({
  name: 'generate-user-roles',
  type: ASYNC_BUTTON,
  label: trans('generate_user_roles', {}, 'actions'),
  request: {
    url: ['apiv2_role_generate_all_user_roles'],
    request: {method: 'POST'}
  },
  displayed: toolConstants.TOOL_DESKTOP === tools[0].contextType && hasPermission('administrate', tools[0]),
  scope: ['object'],
  group: trans('management'),
  set: [constants.ACTION_SET_ADVANCED],
  title: trans('generate_user_roles', {}, 'actions'),
  description: trans('generate_user_roles_desc', {}, 'actions'),
  labelShort: trans('regenerate', {}, 'actions'),
  managerOnly: true
}))
