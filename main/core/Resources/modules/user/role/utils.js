import {trans} from '#/main/core/translation'

import {PLATFORM_ROLE} from '#/main/core/user/role/constants'

const getPlatformRoles = (roles) => roles.filter(role => PLATFORM_ROLE === role.type).map(role => trans(role.translationKey))

export {
  getPlatformRoles
}
