import {bootstrap} from '#/main/app/bootstrap'

import {ScheduledTaskTool} from '#/main/core/administration/scheduled-task/components/tool.jsx'
import {reducer}           from '#/main/core/administration/scheduled-task/reducer'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.scheduled-tasks-container',

  // app main component
  ScheduledTaskTool,

  // app store configuration
  reducer
)
