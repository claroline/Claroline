import {trans} from '#/main/core/translation'

// TODO : remove individual exports (kept for retro compatibility)

/** @deprecated */
export const PLATFORM_ROLE = 1
/** @deprecated */
export const WS_ROLE = 2
/** @deprecated */
export const CUSTOM_ROLE = 3
/** @deprecated */
export const USER_ROLE = 4

/** @deprecated */
export const enumRole = {
  [PLATFORM_ROLE]: trans('platform'),
  [WS_ROLE]: trans('workspace'),
  [CUSTOM_ROLE]: trans('custom'),
  [USER_ROLE]: trans('user')
}

// TODO : should be a string for better data readability
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
