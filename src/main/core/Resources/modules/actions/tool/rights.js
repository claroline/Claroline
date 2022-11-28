import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {constants} from '#/main/core/tool/constants'
import {MODAL_TOOL_RIGHTS} from '#/main/core/tool/modals/rights'

/**
 * Displays a form to configure the rights of a tool.
 */
export default (tool, context, toolRefresher) => ({
  name: 'rights',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-lock',
  label: trans('edit-rights', {}, 'actions'),
  modal: [MODAL_TOOL_RIGHTS, {
    toolName: tool.name,
    currentContext: context,
    onSave: () => toolRefresher.update(tool)
  }],
  displayed: -1 !== [constants.TOOL_DESKTOP, constants.TOOL_WORKSPACE].indexOf(context.type) && hasPermission('administrate', tool),
  group: trans('management')
})
