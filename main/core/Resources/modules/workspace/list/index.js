import {bootstrap} from '#/main/app/bootstrap'

import {reducer} from '#/main/core/workspace/list/reducer'
import {Workspaces} from '#/main/core/workspace/list/components/list.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.workspaces-container',

  // app main component
  Workspaces,

  // app store configuration
  reducer
)
