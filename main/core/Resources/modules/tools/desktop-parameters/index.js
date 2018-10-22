import {bootstrap} from '#/main/app/dom/bootstrap'

import {reducer} from '#/main/core/tools/desktop-parameters/store/reducer'
import {Tool} from '#/main/core/tools/desktop-parameters/components/tool'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.desktop-parameters-container',

  // app main component
  Tool,

  // app store configuration
  reducer,

  (initialData) => ({
    tools: initialData.tools,
    toolsConfig: {
      data: initialData.toolsConfig
    }
  })
)
