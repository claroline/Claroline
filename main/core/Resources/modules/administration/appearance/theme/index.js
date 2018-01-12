import {bootstrap} from '#/main/core/scaffolding/bootstrap'
import {routedApp} from '#/main/core/router'

import {reducer} from '#/main/core/administration/appearance/theme/reducer'
import {actions} from '#/main/core/administration/appearance/theme/actions'

import {Themes} from '#/main/core/administration/appearance/theme/components/themes.jsx'
import {Theme} from '#/main/core/administration/appearance/theme/components/theme.jsx'

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.themes-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  routedApp([
    {
      path: '/',
      component: Themes,
      exact: true
    }, {
      path: '/:id',
      component: Theme,
      onEnter: (params) => actions.editTheme(params.id),
      onLeave: () => actions.resetThemeForm()
    }
  ]),

  // app store configuration
  reducer
)
