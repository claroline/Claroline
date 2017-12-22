import {bootstrap} from '#/main/core/utilities/app/bootstrap'

import {reducer} from './reducer'
import {AnnouncementResource} from './components/resource.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.announcement-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  AnnouncementResource,

  // app store configuration
  reducer
)
