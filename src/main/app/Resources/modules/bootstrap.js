import {bootstrap} from '#/main/app/dom/bootstrap'

import {LayoutMain} from '#/main/app/layout/containers/main'
import {reducer} from '#/main/app/layout/store'

const ClarolineApp = {
  component: LayoutMain,
  store: reducer,
  initialData: (initialData) => ({
    maintenance: initialData.maintenance,
    header: initialData.header,
    footer: initialData.footer,
    config: initialData.config,
    security: {
      impersonated: initialData.impersonated,
      currentUser: initialData.currentUser,
      administration: initialData.administration,
      client: initialData.client
    }
  })
}

// for dev purpose. This allows us to have an understandable name in
// the list of stores in the dev tools
ClarolineApp.component.displayName = 'MainApp'

// mount the whole Claroline Connect application
bootstrap('#claroline-app', ClarolineApp.component, ClarolineApp.store, ClarolineApp.initialData)
