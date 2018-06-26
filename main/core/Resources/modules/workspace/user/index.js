import {bootstrap} from '#/main/app/bootstrap'

import {registerUserTypes} from '#/main/core/user/data'

import {reducer} from '#/main/core/workspace/user/reducer'
import {UserTool} from '#/main/core/workspace/user/components/tool.jsx'

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
