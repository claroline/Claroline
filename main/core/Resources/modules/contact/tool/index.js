import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {reducer} from '#/main/core/contact/tool/reducer'
import {Tool} from '#/main/core/contact/tool/components/tool.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.contacts-container',

  // app main component
  Tool,

  // app store configuration
  reducer,

  (initialData) => ({
    options: {
      data: initialData.options,
      originalData: initialData.options
    }
  })
)
