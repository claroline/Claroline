import {bootstrap} from '#/main/app/bootstrap'

import {BookReferenceResource} from './components/resource.jsx'
import {reducer} from './reducer'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.book-reference-container',

  // app main component
  BookReferenceResource,

  // app store configuration
  {
    bookReference: reducer
  },

  (initialData) => Object.assign({}, initialData, {
    bookReference: {
      data: initialData.bookReference,
      originalData: initialData.bookReference
    },
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  })
)
