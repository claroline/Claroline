import {bootstrap} from '#/main/app/bootstrap'

import {LogTool} from '#/main/core/workspace/logs/components/tool.jsx'
import {reducer} from '#/main/core/workspace/logs/reducer'


// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.logs-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  LogTool,

  // app store configuration
  reducer,
  // initial data
  initialData => ({
    workspaceId: initialData.workspaceId,
    actions: initialData.actions
  })
)
