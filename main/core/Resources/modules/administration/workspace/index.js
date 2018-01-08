import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {reducer} from '#/main/core/administration/workspace/reducer'
import {Workspaces} from '#/main/core/administration/workspace/components/workspaces.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.workspaces-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  Workspaces,

  // app store configuration
  reducer
)
