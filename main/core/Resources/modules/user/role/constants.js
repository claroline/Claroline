import {t} from '#/main/core/translation'

export const PLATFORM_ROLE = 1
export const WS_ROLE = 2
export const CUSTOM_ROLE = 3
export const USER_ROLE = 4

export const enumRole = {
  [PLATFORM_ROLE]: t('platform'),
  [WS_ROLE]: t('workspace'),
  [CUSTOM_ROLE]: t('custom'),
  [USER_ROLE]: t('user')
}
