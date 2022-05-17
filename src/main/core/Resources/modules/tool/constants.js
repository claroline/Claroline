import {trans} from '#/main/app/intl/translation'

// declares types of tools
const TOOL_HOME           = 'home'
const TOOL_DESKTOP        = 'desktop'
const TOOL_WORKSPACE      = 'workspace'
const TOOL_ADMINISTRATION = 'administration'

const TOOL_TYPES = {
  [TOOL_DESKTOP]: trans('desktop_tool'),
  [TOOL_WORKSPACE]: trans('workspace_tool'),
  [TOOL_ADMINISTRATION]: trans('administration_tool')
}

export const constants = {
  TOOL_TYPES,
  TOOL_HOME,
  TOOL_DESKTOP,
  TOOL_WORKSPACE,
  TOOL_ADMINISTRATION
}
