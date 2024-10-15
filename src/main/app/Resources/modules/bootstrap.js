import {bootstrap} from '#/main/app/dom/bootstrap'

import {Platform} from '#/main/app/platform/components/main'
import {reducer} from '#/main/app/platform/store'

// mount the whole Claroline Connect application
bootstrap(
  '#claroline-app',
  Platform,
  reducer,
  (initialData) => ({
    footer: initialData.footer,
    config: initialData.config,
    contexts: initialData.contexts,
    contextFavorites: initialData.contextFavorites,
    currentOrganization: initialData.currentOrganization,
    availableOrganizations: initialData.availableOrganizations,
    security: {
      impersonated: initialData.impersonated,
      currentUser: initialData.currentUser
    }
  }),
  ''
)
