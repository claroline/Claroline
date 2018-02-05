import {registerType} from '#/main/core/data'

import {ORGANIZATION_TYPE, organizationDefinition} from '#/main/core/user/data/types/organization'
import {USERS_TYPE, usersDefinition} from '#/main/core/user/data/types/users'

function registerUserTypes() {
  registerType(ORGANIZATION_TYPE, organizationDefinition)
  registerType(USERS_TYPE,        usersDefinition)
}

export {
  registerUserTypes
}
