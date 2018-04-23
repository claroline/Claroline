import {bootstrap} from '#/main/app/bootstrap'

import {reducer} from '#/main/core/user/contact/reducer'
import {Tool} from '#/main/core/user/contact/components/tool'

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
