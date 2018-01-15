import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {decorate} from '#/main/core/user/profile/decorator'
import {reducer} from '#/main/core/user/profile/reducer'
import {Profile} from '#/main/core/user/profile/components/main.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.user-profile-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  Profile,

  // app store configuration
  reducer,

  (initialData) => Object.assign({}, initialData, {
    user: {
      data: initialData.user,
      originalData: initialData.user
    },
    facets: decorate(initialData.facets)
  })
)
