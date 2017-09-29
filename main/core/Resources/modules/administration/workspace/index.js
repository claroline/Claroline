import merge from 'lodash/merge'

import {bootstrap} from '#/main/core/utilities/app/bootstrap'
import {generateUrl} from '#/main/core/fos-js-router'

// reducers
import {reducer as apiReducer} from '#/main/core/api/reducer'
import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'
import {reducer} from '#/main/core/administration/workspace/reducer'

import {Workspaces} from '#/main/core/administration/workspace/components/workspaces.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.workspaces-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  Workspaces,

  // app store configuration
  {
    // app reducers
    workspaces: reducer,

    // generic reducers
    currentRequests: apiReducer,
    modal: modalReducer
  },

  // remap data-attributes set on the app DOM container
  (initialData) => ({
    workspaces: merge({}, initialData.workspaces, {
      fetchUrl: generateUrl('api_get_search_workspaces')
    })
  })
)
