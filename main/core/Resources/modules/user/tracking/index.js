import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {reducer} from '#/main/core/user/tracking/reducer'
import {Tracking} from '#/main/core/user/tracking/components/main.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.user-tracking-container',

  // app main component
  Tracking,

  // app store configuration
  reducer
)
