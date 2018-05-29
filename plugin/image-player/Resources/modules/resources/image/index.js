import {bootstrap} from '#/main/app/bootstrap'

import {Image} from './components/image.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.image-container',

  // app main component
  Image,

  // app store configuration
  {
    // there is no editor for now, so we just init a static store
    image: (state = {}) => state
  },
  (initialData) => (Object.assign({}, initialData, {
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  }))
)
