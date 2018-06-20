import {bootstrap} from '#/main/app/bootstrap'

import {reducer} from '#/plugin/agenda/reducer'
import {Agenda} from '#/plugin/agenda/components/agenda.jsx'


// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.agenda-container',

  // app main component
  Agenda,

  // app store configuration
  reducer,

  // remap data-attributes set on the app DOM container
  // todo load remaining through ajax
  (initialData) => {
    return {
      workspace: initialData.workspace,
      workspaces: initialData.workspaces,
      filters: {
        workspaces: initialData.workspace.uuid ? [initialData.workspace.uuid]: Object.keys(initialData.workspaces)
      }
    }
  }
)
