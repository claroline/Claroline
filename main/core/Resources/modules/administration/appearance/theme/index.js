import {bootstrap} from '#/main/core/scaffolding/bootstrap'

import {reducer} from '#/main/core/administration/appearance/theme/reducer'
import {ThemeTool} from '#/main/core/administration/appearance/theme/components/tool.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.themes-container',

  // app main component
  ThemeTool,

  // app store configuration
  reducer
)
