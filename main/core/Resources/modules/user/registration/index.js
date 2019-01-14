import {bootstrap} from '#/main/app/dom/bootstrap'

// reducers
import {reducer} from '#/main/core/user/registration/reducer'
import {UserRegistration} from '#/main/core/user/registration/components/main.jsx'

// mount the react application
bootstrap(
  '.user-registration-container',
  UserRegistration,
  reducer,
  (initialData) => Object.assign({}, initialData, {
    workspaces: {
      selected: initialData.defaultWorkspaces.map(workspace => workspace.id)
    },
    user: {
      data: {
        roles: initialData.defaultWorkspaces.map(workspace => workspace.registration.defaultRole)
      }
    }
  })
)
