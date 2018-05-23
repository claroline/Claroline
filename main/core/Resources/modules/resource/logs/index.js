import {bootstrap} from '#/main/app/bootstrap'

import {LogTool} from '#/main/core/resource/logs/components/tool.jsx'
import {reducer} from '#/main/core/resource/logs/reducer'


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
    resourceId: initialData.resourceId,
    actions: initialData.actions
  })
)
