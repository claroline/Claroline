import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {registerModals} from '#/main/core/layout/modal'

import {registerUserTypes} from '#/main/core/user/data'

import {reducer} from '#/main/core/workspace/user/reducer'
import {UserTool} from '#/main/core/workspace/user/components/tool.jsx'
import {MODAL_CONFIRM_REGISTRATION, ConfirmRegistrationModal} from '#/main/core/workspace/user/pending/components/modal/confirm-registration.jsx'

registerModals([
  [MODAL_CONFIRM_REGISTRATION, ConfirmRegistrationModal]
])
registerUserTypes()

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.workspaces-container',

  // app main component
  UserTool,

  // app store configuration
  reducer,

  // remap data-attributes set on the app DOM container
  // todo load remaining through ajax
  (initialData) => {
    return {
      workspace: initialData.workspace,
      restrictions: initialData.restrictions,
      parameters: {
        data: initialData.workspace,
        originalData: initialData.workspace
      }
    }
  }
)
