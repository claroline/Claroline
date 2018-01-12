import {bootstrap} from '#/main/core/scaffolding/bootstrap'

// reducers
import {makeResourceReducer} from '#/main/core/resource/reducer'

import {BookReference} from './components/book-reference.jsx'
import {reducer} from './reducer'
import { makeFormReducer } from '#/main/core/data/form/reducer'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.book-reference-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  BookReference,

  // app store configuration
  makeResourceReducer({}, {
    // there is no editor for now, so we just init a static store
    bookReference: reducer,
    bookReferenceForm: makeFormReducer('bookReferenceForm')
  })
)
