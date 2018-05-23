import {bootstrap} from '#/main/app/bootstrap'

import {AnalyticsTool} from '#/main/core/administration/analytics/components/tool.jsx'
import {reducer} from '#/main/core/administration/analytics/reducer'


// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.analytics-container',
  
  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  AnalyticsTool,
  
  // app store configuration
  reducer
)