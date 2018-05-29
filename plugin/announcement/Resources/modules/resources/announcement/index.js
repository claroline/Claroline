import {bootstrap} from '#/main/app/bootstrap'

import {reducer} from './reducer'
import {AnnouncementResource} from './components/resource.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.announcement-container',

  // app main component
  AnnouncementResource,

  // app store configuration
  reducer,

  (initialData) => Object.assign({}, initialData, {
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  })
)
