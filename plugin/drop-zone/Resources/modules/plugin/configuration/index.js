import {bootstrap} from '#/main/app/dom/bootstrap'

import {reducer} from '#/plugin/drop-zone/plugin/configuration/reducer'
import {Tools} from '#/plugin/drop-zone/plugin/configuration/components/tools.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.dropzone-plugin-container',

  // app main component
  Tools,

  // app store configuration
  reducer
)
