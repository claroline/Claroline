import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {reducer} from '#/main/core/resources/text/reducer'
import {TextResource} from '#/main/core/resources/text/components/resource.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.text-resource-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  TextResource,

  // app store configuration
  reducer
)