import {trans} from '#/main/app/intl/translation'

// TODO : should be a string for better data readability (requires api changes)
const ROLE_PLATFORM  = 1
const ROLE_WORKSPACE = 2
const ROLE_CUSTOM    = 3
const ROLE_USER      = 4

const ROLE_TYPES = {
  [ROLE_PLATFORM] : trans('platform'),
  [ROLE_WORKSPACE]: trans('workspace'),
  [ROLE_CUSTOM]   : trans('custom'),
  [ROLE_USER]     : trans('user')
}

export const constants = {
  ROLE_TYPES,
  ROLE_PLATFORM,
  ROLE_WORKSPACE,
  ROLE_CUSTOM,
  ROLE_USER
}
