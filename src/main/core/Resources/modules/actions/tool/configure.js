import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'

import {constants} from '#/main/core/tool/constants'

/**
 * Displays a form to modify tool properties.
 */
export default (tool, context, toolRefresher, path) => ({
  name: 'configure',
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  target: path + '/edit',
  displayed: -1 !== [constants.TOOL_DESKTOP, constants.TOOL_WORKSPACE].indexOf(context.type) && hasPermission('edit', tool),
  group: trans('management')
})
