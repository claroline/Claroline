import {bootstrap} from '#/main/core/utilities/app/bootstrap'
import {routedApp} from '#/main/core/utilities/app/router'

import {reducer as apiReducer} from '#/main/core/api/reducer'
import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'
import {makeListReducer} from '#/main/core/layout/list/reducer'
import {reducer as themesReducer} from '#/main/core/administration/theme/reducer'

import {Themes} from '#/main/core/administration/theme/components/themes.jsx'
import {Theme} from '#/main/core/administration/theme/components/theme.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.themes-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  routedApp([
    {path: '/',    component: Themes, exact: true},
    {path: '/:id', component: Theme}
  ]),

  // app store configuration
  {
    // app reducers
    themes: themesReducer,

    // generic reducers
    currentRequests: apiReducer,
    modal: modalReducer,
    list: makeListReducer(false) // disable filters
  }
)
