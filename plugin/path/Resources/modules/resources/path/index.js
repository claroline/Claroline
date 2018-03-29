import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {reducer} from '#/plugin/path/resources/path/reducer'

import {PathResource} from '#/plugin/path/resources/path/components/resource.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.path-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  PathResource,

  // app store configuration
  reducer,

  initialData => Object.assign({}, initialData, {
    summary: {
      opened: initialData.path.display.openSummary,
      pinned: initialData.path.display.openSummary
    },
    pathForm: {
      data: initialData.path
    }
  })
)
