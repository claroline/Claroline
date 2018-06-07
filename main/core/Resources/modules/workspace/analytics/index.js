import {bootstrap} from '#/main/app/bootstrap'

import {DashboardTool} from '#/main/core/workspace/analytics/components/tool'
import {reducer} from '#/main/core/workspace/analytics/reducer'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.dashboard-container',
  
  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  DashboardTool,
  
  // app store configuration
  reducer
)
