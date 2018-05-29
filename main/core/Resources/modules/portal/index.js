import {bootstrap} from '#/main/app/bootstrap'

import {reducer} from '#/main/core/portal/reducer'
import {Portal} from '#/main/core/portal/components/portal'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.portal-container',

  // app main component
  Portal,

  // app store configuration
  reducer
)
