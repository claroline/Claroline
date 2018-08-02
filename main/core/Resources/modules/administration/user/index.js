import {bootstrap} from '#/main/app/bootstrap'

import {decorate} from '#/main/core/user/profile/decorator'

import {reducer} from '#/main/core/administration/user/reducer'
import {UserTool} from '#/main/core/administration/user/components/tool'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.users-container',

  // app main component
  UserTool,

  // app store configuration
  reducer,

  // remap data-attributes set on the app DOM container
  // todo load remaining through ajax
  (initialData) => {
    const profileFacets = decorate(initialData.profile)

    return {
      parameters: {
        data: initialData.parameters,
        originalData: initialData.parameters
      },
      profile: {
        data: profileFacets,
        originalData: profileFacets
      },
      platformRoles: initialData.platformRoles.data
    }
  }
)
