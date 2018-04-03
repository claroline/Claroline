import {registerType} from '#/main/core/data'

import {MESSAGE_TYPE, messageDefinition} from '#/plugin/planned-notification/data/types/message'
import {WORKSPACE_ROLES_TYPE, rolesDefinition} from '#/plugin/planned-notification/data/types/roles'

function registerPlannedNotificationTypes() {
  registerType(MESSAGE_TYPE,  messageDefinition)
  registerType(WORKSPACE_ROLES_TYPE,  rolesDefinition)
}

export {
  registerPlannedNotificationTypes
}