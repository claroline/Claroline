import {bootstrap} from '#/main/app/bootstrap'

import {reducer} from '#/main/core/administration/workspace/reducer'
import {WorkspaceTool} from '#/main/core/administration/workspace/components/tool'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.workspaces-container',

  // app main component
  WorkspaceTool,

  // app store configuration
  reducer,

  // remap data-attributes set on the app DOM container
  // todo load remaining through ajax
  (initialData) => {

    return {
      parameters: {
        data: initialData.parameters,
        originalData: initialData.parameters
      },
      tools: initialData.tools,
      models: initialData.models
    }
  }
)
