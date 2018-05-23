import {bootstrap} from '#/main/app/bootstrap'

import {LogTool} from '#/main/core/administration/logs/components/tool.jsx'
import {reducer} from '#/main/core/administration/logs/reducer'


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
    actions: initialData.actions
  })
)
