import {bootstrap} from '#/main/core/utilities/app/bootstrap'

import {reducer} from '#/main/core/portal/reducer'
import {Portal} from '#/main/core/portal/components/portal.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.portal-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  Portal,

  // app store configuration
  reducer
)
