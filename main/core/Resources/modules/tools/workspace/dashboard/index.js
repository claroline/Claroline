import {bootstrap} from '#/main/app/bootstrap'

import {DashboardTool} from '#/main/core/tools/workspace/dashboard/components/tool.jsx'
import {reducer} from '#/main/core/tools/workspace/dashboard/reducer'


// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.dashboard-container',
  
  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  DashboardTool,
  
  // app store configuration
  reducer,
  // initial data
  initialData => ({
    workspaceId: initialData.workspaceId
  })
)