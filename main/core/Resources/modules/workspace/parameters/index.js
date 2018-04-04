import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {reducer} from '#/main/core/workspace/parameters/reducer'
import {Parameters} from '#/main/core/workspace/parameters/components/tool.jsx'


// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.workspaces-container',

  // app main component
  Parameters,

  // app store configuration
  reducer,

  // remap data-attributes set on the app DOM container
  // todo load remaining through ajax
  (initialData) => {
    return {
      parameters: {
        data: initialData.workspace,
        originalData: initialData.workspace
      }
    }
  }
)
