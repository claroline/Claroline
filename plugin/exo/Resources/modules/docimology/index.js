import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {Docimology} from './components/docimology.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.docimology-container',

  // app main component
  Docimology,

  // app store configuration
  {
    // app reducers
    quiz: (state = {}) => state,
    statistics: (state = {}) => state
  }
)
