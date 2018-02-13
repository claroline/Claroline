import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {reducer} from '#/main/core/resource/portal/reducer'
import {Portal} from '#/main/core/resource/portal/components/portal.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.portal-container',

  // app main component
  Portal,

  // app store configuration
  reducer
)
