import {registerType} from '#/main/core/data'

import {ORGANIZATION_TYPE, organizationDefinition} from '#/main/core/user/data/types/organization'

function registerUserTypes() {
  registerType(ORGANIZATION_TYPE,  organizationDefinition)
}

export {
  registerUserTypes
}
