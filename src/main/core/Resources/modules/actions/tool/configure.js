import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {constants} from '#/main/core/tool/constants'
import {MODAL_TOOL_PARAMETERS} from '#/main/core/tool/modals/parameters'

/**
 * Displays a form to modify tool properties.
 */
export default (tool, context, toolRefresher, path) => ({
  name: 'configure',
  /*type: MODAL_BUTTON,*/
  type: LINK_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  target: path + '/edit',
  /*modal: [MODAL_TOOL_PARAMETERS, {
    toolName: tool.name,
    currentContext: context,
    data: tool,
    onSave: (updatedData) => toolRefresher.update(updatedData)
  }],*/
  displayed: -1 !== [constants.TOOL_DESKTOP, constants.TOOL_WORKSPACE].indexOf(context.type) && hasPermission('edit', tool),
  group: trans('management')
})
