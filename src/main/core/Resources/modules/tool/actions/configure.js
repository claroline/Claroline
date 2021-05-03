import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_TOOL_PARAMETERS} from '#/main/core/tool/modals/parameters'

/**
 * Displays a form to modify tool properties.
 */
export default (tool, context, toolRefresher) => ({
  name: 'configure',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  modal: [MODAL_TOOL_PARAMETERS, {
    toolName: tool.name,
    currentContext: context,
    data: tool,
    onSave: (updatedData) => toolRefresher.update(updatedData)
  }],
  displayed: 'administration' !== context.type && hasPermission('edit', tool),
  group: trans('management')
})
