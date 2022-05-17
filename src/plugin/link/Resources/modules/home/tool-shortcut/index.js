import {constants as toolConst} from '#/main/core/tool/constants'

import {ToolShortcutTab} from '#/plugin/link/home/tool-shortcut/components/tab'
import {ToolShortcutTabParameters} from '#/plugin/link/home/tool-shortcut/containers/parameters'

export default {
  name: 'tool_shortcut',
  icon: 'fa fa-fw fa-tools',
  context: [toolConst.TOOL_DESKTOP, toolConst.TOOL_WORKSPACE, toolConst.TOOL_ADMINISTRATION],
  class: 'Claroline\\LinkBundle\\Entity\\Home\\ToolShortcutTab',
  component: ToolShortcutTab,
  parameters: ToolShortcutTabParameters
}
