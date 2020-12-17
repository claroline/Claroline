import {bootstrap} from '#/main/app/dom/bootstrap'

// reducers
import {reducer} from '#/main/app/security/registration/store/reducer'
import {UserRegistration} from '#/main/app/security/registration/components/main'

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
        code: initialData.code,
        roles: initialData.defaultWorkspaces.map(workspace => workspace.registration.defaultRole)
      }
    }
  })
)
