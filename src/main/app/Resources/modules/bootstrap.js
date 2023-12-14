import {bootstrap} from '#/main/app/dom/bootstrap'

import {LayoutMain} from '#/main/app/layout/containers/main'
import {reducer} from '#/main/app/layout/store'

// for dev purpose. This allows us to have an understandable name in
// the list of stores in the dev tools
LayoutMain.displayName = 'MainApp'

// mount the whole Claroline Connect application
bootstrap(
  '#claroline-app',
  LayoutMain,
  reducer,
  (initialData) => ({
    maintenance: initialData.maintenance,
    header: initialData.header,
    footer: initialData.footer,
    config: initialData.config,
    contexts: initialData.contexts,
    security: {
      impersonated: initialData.impersonated,
      currentUser: initialData.currentUser,
      client: initialData.client
    }
  }),
  ''
)
