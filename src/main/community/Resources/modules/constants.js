import {trans} from '#/main/app/intl/translation'

const ROLE_PLATFORM  = 'platform'
const ROLE_WORKSPACE = 'workspace'
const ROLE_USER      = 'user'

const ROLE_TYPES = {
  [ROLE_PLATFORM] : trans('platform'),
  [ROLE_WORKSPACE]: trans('workspace'),
  [ROLE_USER]     : trans('user')
}

export const constants = {
  ROLE_TYPES,
  ROLE_PLATFORM,
  ROLE_WORKSPACE,
  ROLE_USER
}
