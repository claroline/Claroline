import {trans} from '#/main/app/intl/translation'

// declares types of tools
const TOOL_PUBLIC         = 'public'
const TOOL_DESKTOP        = 'desktop'
const TOOL_WORKSPACE      = 'workspace'
const TOOL_ADMINISTRATION = 'administration'
const TOOL_ACCOUNT        = 'account'

const TOOL_TYPES = {
  [TOOL_PUBLIC]: trans('public_tool'),
  [TOOL_DESKTOP]: trans('desktop_tool'),
  [TOOL_WORKSPACE]: trans('workspace_tool'),
  [TOOL_ADMINISTRATION]: trans('administration_tool'),
  [TOOL_ACCOUNT]: trans('account_tool')
}

export const constants = {
  TOOL_TYPES,
  TOOL_PUBLIC,
  TOOL_DESKTOP,
  TOOL_WORKSPACE,
  TOOL_ADMINISTRATION,
  TOOL_ACCOUNT
}
