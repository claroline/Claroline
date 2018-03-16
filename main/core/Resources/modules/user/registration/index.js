import {bootstrap} from '#/main/core/scaffolding/bootstrap'

// reducers
import {reducer} from '#/main/core/user/registration/reducer'
import {UserRegistration} from '#/main/core/user/registration/components/main.jsx'

const getCollaboratorRole = (workspace) => workspace.roles.find(role => role.name.indexOf('COLLABORATOR') > -1)

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
        roles: initialData.defaultWorkspaces.map(workspace => getCollaboratorRole(workspace))
      }
    }
  })
)
