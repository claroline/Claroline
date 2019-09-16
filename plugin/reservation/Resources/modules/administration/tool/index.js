import {bootstrap} from '#/main/app/dom/bootstrap'

import {reducer} from '#/plugin/reservation/administration/tool/reducer'
import {ReservationTool} from '#/plugin/reservation/administration/tool/components/tool.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.reservation-tool-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  ReservationTool,

  // app store configuration
  reducer
)