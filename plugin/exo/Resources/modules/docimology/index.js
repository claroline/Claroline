import {bootstrap} from '#/main/app/bootstrap'

import {Docimology} from '#/plugin/exo/docimology/components/docimology'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.docimology-container',

  // app main component
  Docimology,

  // app store configuration
  {
    // app reducers
    resourceNode: (state = {}) => state,
    quiz: (state = {}) => state,
    statistics: (state = {}) => state
  }
)
