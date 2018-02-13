import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {registerType} from '#/main/core/data'
import {FIELDS_TYPE, fieldsDefinition} from '#/main/core/data/types/fields'

import {registerUserTypes} from '#/main/core/user/data'

import {decorate} from '#/main/core/user/profile/decorator'

import {reducer} from '#/main/core/administration/user/reducer'
import {UserTool} from '#/main/core/administration/user/components/tool.jsx'

import {registerModals} from '#/main/core/layout/modal'

import {MODAL_CHANGE_PASSWORD, ChangePasswordModal} from '#/main/core/user/modals/components/change-password.jsx'

// register dynamic fields type
registerType(FIELDS_TYPE,  fieldsDefinition)

// register user modals
registerModals([
  [MODAL_CHANGE_PASSWORD, ChangePasswordModal]
])

// register user form fields
registerUserTypes()

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.users-container',

  // app main component
  UserTool,

  // app store configuration
  reducer,

  // remap data-attributes set on the app DOM container
  // todo load remaining through ajax
  (initialData) => {
    const profileFacets = decorate(initialData.profile)

    return {
      parameters: {
        data: initialData.parameters,
        originalData: initialData.parameters
      },
      profile: {
        data: profileFacets,
        originalData: profileFacets
      }
    }
  }
)
