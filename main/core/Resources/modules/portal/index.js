import merge from 'lodash/merge'

import {bootstrap} from '#/main/core/utilities/app/bootstrap'
import {generateUrl} from '#/main/core/fos-js-router'

// reducers
import {reducer as apiReducer} from '#/main/core/api/reducer'
import {reducer as modalReducer} from '#/main/core/layout/modal/reducer'

import {reducer} from '#/main/core/portal/reducer'

import {Portal} from '#/main/core/portal/components/portal.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.portal-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  Portal,

  // app store configuration
  {
    // app reducers
    portal: reducer,

    // generic reducers
    currentRequests: apiReducer,
    modal: modalReducer
  },

  // remap data-attributes set on the app DOM container
  (initialData) => ({
    portal: merge({}, initialData.portal, {
      fetchUrl: generateUrl('apiv2_portal_index')
    })
  })
)

